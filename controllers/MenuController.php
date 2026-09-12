<?php
// =============================================
// RoomScan — Menu Controller (Room Service Menu)
// =============================================

class MenuController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /menu
  public function index(?string $param = null) {
    $hotelId = current_hotel_id();
    $categoryFilter = trim($_GET['category'] ?? '');

    $sql = "SELECT * FROM menu_items WHERE hotel_id = ?";
    $params = [$hotelId];

    if ($categoryFilter !== '') {
      $sql .= " AND category = ?";
      $params[] = $categoryFilter;
    }
    $sql .= " ORDER BY category, name";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Distinct categories for filter pills
    $stmt = $this->db->prepare("SELECT DISTINCT category FROM menu_items WHERE hotel_id = ? AND category IS NOT NULL AND category != '' ORDER BY category");
    $stmt->execute([$hotelId]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $title = 'Menu Room Service — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/menu/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /menu/create
  public function create(?string $param = null) {
    $hotelId = current_hotel_id();
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $name = trim(input('name', ''));
        $description = trim(input('description', ''));
        $price = (float) input('price', 0);
        $category = trim(input('category', 'Autre'));
        $prepTime = (int) input('prep_time', 15);
        $available = input('available') ? 1 : 0;

        if (!$name || $price <= 0) {
          $error = 'Le nom et un prix valide sont requis.';
        } else {
          // Image upload (optional)
          $imagePath = null;
          if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleUpload('image', 'menu_tmp');
          }

          $stmt = $this->db->prepare(
            "INSERT INTO menu_items (hotel_id, name, description, price, category, image, available, prep_time)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
          );
          $stmt->execute([
            $hotelId, $name, $description, $price, $category, $imagePath, $available, $prepTime
          ]);

          // Rename image with proper id prefix if uploaded
          if ($imagePath) {
            $newId = (int) $this->db->lastInsertId();
            $newPath = '/public/uploads/menu_' . $newId . '_' . time() . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
            $oldFile = __DIR__ . '/../public/uploads/' . basename($imagePath);
            $newFile = __DIR__ . '/../public/uploads/' . basename($newPath);
            if (file_exists($oldFile) && rename($oldFile, $newFile)) {
              $stmt = $this->db->prepare("UPDATE menu_items SET image = ? WHERE id = ?");
              $stmt->execute([$newPath, $newId]);
            }
          }

          redirect('/menu');
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Nouvel article menu — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/menu/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /menu/edit/{id}
  public function edit(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    // Fetch item
    $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $item = $stmt->fetch();

    if (!$item) {
      redirect('/menu');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $name = trim(input('name', ''));
        $description = trim(input('description', ''));
        $price = (float) input('price', 0);
        $category = trim(input('category', 'Autre'));
        $prepTime = (int) input('prep_time', 15);
        $available = input('available') ? 1 : 0;

        if (!$name || $price <= 0) {
          $error = 'Le nom et un prix valide sont requis.';
        } else {
          $imagePath = $item['image'];
          if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleUpload('image', 'menu_' . $id);
            if ($uploaded !== null) {
              // Optionally delete old image
              if (!empty($item['image'])) {
                $oldFile = __DIR__ . '/../public/uploads/' . basename($item['image']);
                if (is_file($oldFile)) @unlink($oldFile);
              }
              $imagePath = $uploaded;
            } else {
              $error = 'Image invalide (format jpg/png/webp, max 2 Mo).';
            }
          }

          if (!$error) {
            $stmt = $this->db->prepare(
              "UPDATE menu_items
               SET name = ?, description = ?, price = ?, category = ?, image = ?, available = ?, prep_time = ?
               WHERE id = ? AND hotel_id = ?"
            );
            $stmt->execute([
              $name, $description, $price, $category, $imagePath, $available, $prepTime,
              $id, $hotelId
            ]);
            redirect('/menu');
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Modifier article — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/menu/edit.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /menu/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!verify_csrf()) {
      redirect('/menu');
    }

    // Fetch item to optionally remove image file
    $stmt = $this->db->prepare("SELECT image FROM menu_items WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $item = $stmt->fetch();
    if ($item && !empty($item['image'])) {
      $oldFile = __DIR__ . '/../public/uploads/' . basename($item['image']);
      if (is_file($oldFile)) @unlink($oldFile);
    }

    $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    redirect('/menu');
  }

  // POST /menu/toggle/{id}
  public function toggle(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!verify_csrf()) {
      redirect('/menu');
    }

    $stmt = $this->db->prepare(
      "UPDATE menu_items SET available = 1 - available WHERE id = ? AND hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    redirect('/menu');
  }

  // ---- Helpers ----

  /**
   * Handle a file upload. Validates type & size.
   * Returns the public URL on success, or null on failure.
   */
  private function handleUpload(string $field, string $basenamePrefix): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
      return null;
    }
    $file = $_FILES[$field];

    if ($file['size'] > 2 * 1024 * 1024) {
      return null;
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowedMimes[$mime])) {
      return null;
    }
    $ext = $allowedMimes[$mime];

    $filename = $basenamePrefix . '_' . time() . '.' . $ext;
    $dest = __DIR__ . '/../public/uploads/' . $filename;

    if (!is_dir(dirname($dest))) {
      @mkdir(dirname($dest), 0775, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      return null;
    }

    return '/public/uploads/' . $filename;
  }
}
