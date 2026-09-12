<?php
// =============================================
// RoomScan — Lost & Found Controller
// =============================================

class LostFoundController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /lostfound — list with filters (type, status, search)
  public function index() {
    $hotelId     = current_hotel_id();
    $typeFilter  = input('type', '');
    $statusFilter = input('status', '');
    $search      = trim(input('q', ''));

    $sql = "SELECT lf.*, r.number as room_number
            FROM lost_found lf
            LEFT JOIN rooms r ON lf.room_id = r.id
            WHERE lf.hotel_id = ?";
    $params = [$hotelId];

    if (in_array($typeFilter, ['lost', 'found'], true)) {
      $sql .= " AND lf.type = ?";
      $params[] = $typeFilter;
    }
    if (in_array($statusFilter, ['open', 'claimed', 'returned', 'disposed'], true)) {
      $sql .= " AND lf.status = ?";
      $params[] = $statusFilter;
    }
    if ($search !== '') {
      $sql .= " AND lf.item_name LIKE ?";
      $params[] = '%' . $search . '%';
    }
    $sql .= " ORDER BY lf.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $title = 'Objets trouvés — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/lostfound/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /lostfound/view/{id}
  public function view(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $item = $this->findItem($id, $hotelId);
    if (!$item) {
      $this->notFound();
      return;
    }

    $csrf = csrf_token();
    $title = $item['item_name'] . ' — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/lostfound/view.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /lostfound/create
  public function create() {
    $this->form(null);
  }

  // GET/POST /lostfound/edit/{id}
  public function edit(?string $id) {
    $this->form($id);
  }

  private function form(?string $idParam) {
    $hotelId = current_hotel_id();
    $error   = '';
    $isEdit  = !empty($idParam);
    $id      = (int) $idParam;

    $item = [
      'id'          => null,
      'type'        => 'found',
      'item_name'   => '',
      'description' => '',
      'room_id'     => null,
      'location'    => '',
      'photo'       => '',
      'status'      => 'open',
      'reported_by' => '',
      'contact'     => '',
      'notes'       => '',
    ];

    if ($isEdit) {
      $found = $this->findItem($id, $hotelId);
      if (!$found) {
        $this->notFound();
        return;
      }
      $item = $found;
    }

    // Rooms list
    $stmt = $this->db->prepare("SELECT id, number FROM rooms WHERE hotel_id = ? ORDER BY number");
    $stmt->execute([$hotelId]);
    $rooms = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $type        = input('type', 'found');
        if (!in_array($type, ['lost', 'found'], true)) $type = 'found';
        $itemName    = trim(input('item_name', ''));
        $description = trim(input('description', ''));
        $roomIdRaw   = input('room_id', '');
        $roomId      = $roomIdRaw !== '' ? (int) $roomIdRaw : null;
        $location    = trim(input('location', ''));
        $reportedBy  = trim(input('reported_by', ''));
        $contact     = trim(input('contact', ''));
        $notes       = trim(input('notes', ''));

        if ($itemName === '') {
          $error = 'Le nom de l\'objet est requis.';
        } else {
          $photoPath = $item['photo'] ?? '';
          if (!empty($_FILES['photo']['name'])) {
            $uploaded = $this->uploadPhoto($_FILES['photo'], $error);
            if ($uploaded) {
              if ($photoPath && $isEdit) {
                $this->deleteUpload($photoPath);
              }
              $photoPath = $uploaded;
            }
          }

          if (!$error) {
            if ($isEdit) {
              $stmt = $this->db->prepare(
                "UPDATE lost_found
                 SET type = ?, item_name = ?, description = ?, room_id = ?, location = ?,
                     photo = ?, reported_by = ?, contact = ?, notes = ?
                 WHERE id = ? AND hotel_id = ?"
              );
              $stmt->execute([
                $type, $itemName, $description, $roomId, $location,
                $photoPath, $reportedBy, $contact, $notes,
                $id, $hotelId,
              ]);
            } else {
              $stmt = $this->db->prepare(
                "INSERT INTO lost_found
                 (hotel_id, type, item_name, description, room_id, location, photo, status, reported_by, contact, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?, ?, ?)"
              );
              $stmt->execute([
                $hotelId, $type, $itemName, $description, $roomId, $location,
                $photoPath, $reportedBy, $contact, $notes,
              ]);
              $id = (int) $this->db->lastInsertId();
            }
            redirect('/lostfound/view/' . $id);
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = ($isEdit ? 'Modifier' : 'Signaler') . ' un objet — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/lostfound/form.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /lostfound/claim/{id} — handle status transitions: claim / return / dispose / reopen
  public function claim(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      redirect('/lostfound');
    }
    if (!verify_csrf()) {
      redirect('/lostfound/view/' . $id);
    }

    $item = $this->findItem($id, $hotelId);
    if (!$item) {
      $this->notFound();
      return;
    }

    $action = input('action', 'claim');
    $validActions = ['claim', 'return', 'dispose', 'reopen'];
    if (!in_array($action, $validActions, true)) {
      $action = 'claim';
    }
    $statusMap = [
      'claim'   => 'claimed',
      'return'  => 'returned',
      'dispose' => 'disposed',
      'reopen'  => 'open',
    ];
    $newStatus = $statusMap[$action];

    $notes = (string) ($item['notes'] ?? '');
    if ($action === 'claim') {
      $claimant        = trim(input('claimant_name', ''));
      $claimantContact = trim(input('claimant_contact', ''));
      $extraNotes       = trim(input('notes', ''));
      if ($claimant === '') {
        // No claimant → reject silently
        redirect('/lostfound/view/' . $id);
      }
      $line = 'Réclamé par : ' . $claimant;
      if ($claimantContact !== '') $line .= ' (' . $claimantContact . ')';
      if ($extraNotes !== '')      $line .= ' — ' . $extraNotes;
      $line .= ' — ' . date('d/m/Y H:i');
    } else {
      $labels = [
        'return'  => 'Rendu au propriétaire',
        'dispose' => 'Éliminé / jeté',
        'reopen'  => 'Dossier rouvert',
      ];
      $line = $labels[$action] . ' — ' . date('d/m/Y H:i');
    }
    $notes = $notes !== '' ? ($notes . "\n" . $line) : $line;

    $stmt = $this->db->prepare(
      "UPDATE lost_found SET status = ?, notes = ? WHERE id = ? AND hotel_id = ?"
    );
    $stmt->execute([$newStatus, $notes, $id, $hotelId]);

    redirect('/lostfound/view/' . $id);
  }

  // GET/POST /lostfound/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    $item = $this->findItem($id, $hotelId);
    if (!$item) {
      $this->notFound();
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        if (!empty($item['photo'])) {
          $this->deleteUpload($item['photo']);
        }
        $stmt = $this->db->prepare("DELETE FROM lost_found WHERE id = ? AND hotel_id = ?");
        $stmt->execute([$id, $hotelId]);
        redirect('/lostfound');
      }
    }

    $csrf = csrf_token();
    $title = 'Supprimer l\'objet — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/lostfound/delete.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // ---------- Helpers ----------

  private function findItem(int $id, int $hotelId): ?array {
    $stmt = $this->db->prepare(
      "SELECT lf.*, r.number as room_number
       FROM lost_found lf
       LEFT JOIN rooms r ON lf.room_id = r.id
       WHERE lf.id = ? AND lf.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  private function uploadPhoto(array $file, string &$error): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $error = 'Erreur lors de l\'upload de la photo.';
      return null;
    }
    if ($file['size'] > $maxSize) {
      $error = 'La photo ne doit pas dépasser 2 Mo.';
      return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes, true)) {
      $error = 'Format non supporté. Utilisez JPG, PNG ou WEBP.';
      return null;
    }
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $dir = __DIR__ . '/../public/uploads/lostfound';
    if (!is_dir($dir)) {
      @mkdir($dir, 0775, true);
    }
    $name = 'lf_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
      $error = 'Impossible d\'enregistrer la photo.';
      return null;
    }
    return '/public/uploads/lostfound/' . $name;
  }

  private function deleteUpload(string $publicPath): void {
    $fullPath = __DIR__ . '/../public' . $publicPath;
    if (is_file($fullPath)) {
      @unlink($fullPath);
    }
  }

  private function notFound(): void {
    http_response_code(404);
    $title = '404 — Objet introuvable';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    echo '<div class="text-center py-20">'
       . '<h1 class="text-6xl font-bold text-slate-300">404</h1>'
       . '<p class="mt-4 text-slate-500">Objet introuvable.</p>'
       . '<a href="/lostfound" class="mt-6 inline-block px-6 py-2 bg-navy-900 text-white rounded-lg">Retour aux objets trouvés</a>'
       . '</div>';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }
}
