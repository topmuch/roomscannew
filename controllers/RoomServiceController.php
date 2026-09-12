<?php
// =============================================
// RoomScan — Room Service Controller
// Manages BOTH menu items and orders.
// =============================================

class RoomServiceController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /roomservice — orders list (with optional status filter)
  public function index() {
    $hotelId = current_hotel_id();
    $statusFilter = input('status', '');
    $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];

    $sql = "SELECT o.*, r.number as room_number
            FROM room_service_orders o
            JOIN rooms r ON o.room_id = r.id
            WHERE o.hotel_id = ?";
    $params = [$hotelId];

    if (in_array($statusFilter, $validStatuses, true)) {
      $sql .= " AND o.status = ?";
      $params[] = $statusFilter;
    }
    $sql .= " ORDER BY FIELD(o.status, 'pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'), o.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Pre-compute items count
    foreach ($orders as &$o) {
      $items = json_decode($o['items'] ?? '[]', true) ?: [];
      $o['items_count'] = count($items);
    }
    unset($o);

    $title = 'Room Service — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/roomservice/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /roomservice/view/{id} — order detail
  public function view(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare(
      "SELECT o.*, r.number as room_number, r.floor as room_floor
       FROM room_service_orders o
       JOIN rooms r ON o.room_id = r.id
       WHERE o.id = ? AND o.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $order = $stmt->fetch();

    if (!$order) {
      $this->notFound('Commande introuvable.');
      return;
    }

    // Parse items and lookup names
    $rawItems = json_decode($order['items'] ?? '[]', true) ?: [];
    $items = [];
    foreach ($rawItems as $ri) {
      $itemId = (int) ($ri['item_id'] ?? 0);
      $stmt = $this->db->prepare("SELECT name, category FROM menu_items WHERE id = ? AND hotel_id = ?");
      $stmt->execute([$itemId, $hotelId]);
      $mi = $stmt->fetch();
      $qty   = (int) ($ri['qty'] ?? 0);
      $price = (float) ($ri['price'] ?? 0);
      $items[] = [
        'name'      => $mi['name'] ?? '(article supprimé)',
        'category'  => $mi['category'] ?? '',
        'qty'       => $qty,
        'price'     => $price,
        'subtotal'  => $price * $qty,
      ];
    }
    $order['items_parsed'] = $items;

    $csrf = csrf_token();
    $title = 'Commande #' . $order['id'] . ' — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/roomservice/view.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /roomservice/update/{id} — change order status
  public function update(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      redirect('/roomservice');
    }
    if (!verify_csrf()) {
      redirect('/roomservice/view/' . $id);
    }

    $status = input('status', '');
    $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    if (!in_array($status, $validStatuses, true)) {
      redirect('/roomservice/view/' . $id);
    }

    // Ensure order belongs to this hotel before updating
    $stmt = $this->db->prepare("SELECT id FROM room_service_orders WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    if (!$stmt->fetch()) {
      $this->notFound('Commande introuvable.');
      return;
    }

    $stmt = $this->db->prepare(
      "UPDATE room_service_orders SET status = ? WHERE id = ? AND hotel_id = ?"
    );
    $stmt->execute([$status, $id, $hotelId]);

    redirect('/roomservice/view/' . $id);
  }

  // GET/POST /roomservice/delete/{id} — delete order (with CSRF)
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    $stmt = $this->db->prepare(
      "SELECT o.*, r.number as room_number
       FROM room_service_orders o
       JOIN rooms r ON o.room_id = r.id
       WHERE o.id = ? AND o.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $order = $stmt->fetch();

    if (!$order) {
      $this->notFound('Commande introuvable.');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $stmt = $this->db->prepare(
          "DELETE FROM room_service_orders WHERE id = ? AND hotel_id = ?"
        );
        $stmt->execute([$id, $hotelId]);
        redirect('/roomservice');
      }
    }

    $csrf = csrf_token();
    $title = 'Supprimer la commande — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/roomservice/delete_order.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // ---------- Menu items ----------

  /**
   * Dispatch nested menu routes:
   *   /roomservice/menu          → menuList
   *   /roomservice/menu/create    → menuForm(null)
   *   /roomservice/menu/edit/{id} → menuForm($id)
   *   /roomservice/menu/delete/{id} → menuDelete($id)
   */
  public function menu(?string $sub = null, ?string $idParam = null) {
    if ($sub === 'create') {
      $this->menuForm(null);
    } elseif ($sub === 'edit') {
      $this->menuForm($idParam);
    } elseif ($sub === 'delete') {
      $this->menuDelete($idParam);
    } else {
      $this->menuList();
    }
  }

  private function menuList() {
    $hotelId = current_hotel_id();

    // Quick toggle availability
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('action') === 'toggle') {
      if (verify_csrf()) {
        $itemId   = (int) input('item_id');
        $available = input('available') === '1' ? 1 : 0;
        $stmt = $this->db->prepare(
          "UPDATE menu_items SET available = ? WHERE id = ? AND hotel_id = ?"
        );
        $stmt->execute([$available, $itemId, $hotelId]);
      }
      redirect('/roomservice/menu');
    }

    $stmt = $this->db->prepare(
      "SELECT * FROM menu_items WHERE hotel_id = ? ORDER BY category, name"
    );
    $stmt->execute([$hotelId]);
    $items = $stmt->fetchAll();

    $csrf = csrf_token();
    $title = 'Menu — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/roomservice/menu.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  private function menuForm(?string $idParam) {
    $hotelId = current_hotel_id();
    $error   = '';
    $isEdit  = !empty($idParam);
    $id      = (int) $idParam;

    $item = [
      'id'          => null,
      'name'        => '',
      'description' => '',
      'price'       => '',
      'category'    => 'breakfast',
      'image'       => '',
      'available'   => 1,
      'prep_time'   => 15,
    ];

    if ($isEdit) {
      $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ? AND hotel_id = ?");
      $stmt->execute([$id, $hotelId]);
      $found = $stmt->fetch();
      if (!$found) {
        $this->notFound('Article introuvable.');
        return;
      }
      $item = $found;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $name        = trim(input('name', ''));
        $description = trim(input('description', ''));
        $price       = (float) str_replace(',', '.', input('price', '0'));
        $category    = input('category', 'breakfast');
        $validCats   = ['breakfast', 'lunch', 'dinner', 'drinks', 'snacks'];
        if (!in_array($category, $validCats, true)) {
          $category = 'breakfast';
        }
        $prepTime  = (int) input('prep_time', 15);
        $available = input('available') === '1' ? 1 : 0;

        if (!$name || $price <= 0) {
          $error = 'Le nom et un prix valide sont requis.';
        } else {
          // Image upload (optional)
          $imagePath = $item['image'] ?? '';
          if (!empty($_FILES['image']['name'])) {
            $uploaded = $this->uploadImage($_FILES['image'], 'menu', $error);
            if ($uploaded) {
              // Delete previous image if replaced
              if ($imagePath && $isEdit) {
                $this->deleteUpload($imagePath);
              }
              $imagePath = $uploaded;
            }
          }

          if (!$error) {
            if ($isEdit) {
              $stmt = $this->db->prepare(
                "UPDATE menu_items
                 SET name = ?, description = ?, price = ?, category = ?,
                     image = ?, available = ?, prep_time = ?
                 WHERE id = ? AND hotel_id = ?"
              );
              $stmt->execute([
                $name, $description, $price, $category,
                $imagePath, $available, $prepTime,
                $id, $hotelId,
              ]);
            } else {
              $stmt = $this->db->prepare(
                "INSERT INTO menu_items
                 (hotel_id, name, description, price, category, image, available, prep_time)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
              );
              $stmt->execute([
                $hotelId, $name, $description, $price, $category,
                $imagePath, $available, $prepTime,
              ]);
            }
            redirect('/roomservice/menu');
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = ($isEdit ? 'Modifier' : 'Ajouter') . ' un article — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/roomservice/menu_form.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  private function menuDelete(?string $idParam) {
    $hotelId = current_hotel_id();
    $id = (int) $idParam;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
      redirect('/roomservice/menu');
    }

    $stmt = $this->db->prepare("SELECT image FROM menu_items WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $item = $stmt->fetch();

    if ($item) {
      if (!empty($item['image'])) {
        $this->deleteUpload($item['image']);
      }
      $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ? AND hotel_id = ?");
      $stmt->execute([$id, $hotelId]);
    }

    redirect('/roomservice/menu');
  }

  // ---------- Helpers ----------

  /**
   * Upload an image to /public/uploads/{type}/.
   * Validates type (jpg/png/webp) and size (<2MB).
   * Returns the public URL path on success, null on failure (sets $error).
   */
  private function uploadImage(array $file, string $type, string &$error): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $error = 'Erreur lors de l\'upload de l\'image.';
      return null;
    }
    if ($file['size'] > $maxSize) {
      $error = 'L\'image ne doit pas dépasser 2 Mo.';
      return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes, true)) {
      $error = 'Format non supporté. Utilisez JPG, PNG ou WEBP.';
      return null;
    }

    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $dir  = __DIR__ . '/../public/uploads/' . $type;
    if (!is_dir($dir)) {
      @mkdir($dir, 0775, true);
    }
    $name = $type . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
      $error = 'Impossible d\'enregistrer l\'image.';
      return null;
    }
    return '/public/uploads/' . $type . '/' . $name;
  }

  private function deleteUpload(string $publicPath): void {
    $fullPath = __DIR__ . '/../public' . $publicPath;
    if (is_file($fullPath)) {
      @unlink($fullPath);
    }
  }

  private function notFound(string $message = 'Ressource introuvable.'): void {
    http_response_code(404);
    $title = '404 — ' . $message;
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    echo '<div class="text-center py-20">'
       . '<h1 class="text-6xl font-bold text-slate-300">404</h1>'
       . '<p class="mt-4 text-slate-500">' . e($message) . '</p>'
       . '<a href="/roomservice" class="mt-6 inline-block px-6 py-2 bg-navy-900 text-white rounded-lg">Retour au room service</a>'
       . '</div>';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }
}
