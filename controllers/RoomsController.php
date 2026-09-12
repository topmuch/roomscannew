<?php
// =============================================
// RoomScan — Rooms Controller (+ embedded Room Types CRUD)
// =============================================

class RoomsController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /rooms
  public function index() {
    $hotelId = current_hotel_id();

    $status = trim((string) input('status', ''));
    $floor  = trim((string) input('floor', ''));
    $typeId = trim((string) input('type_id', ''));

    $sql = "SELECT r.*, rt.name as type_name, rt.capacity as type_capacity, rt.base_price as type_base_price
            FROM rooms r
            LEFT JOIN room_types rt ON r.type_id = rt.id
            WHERE r.hotel_id = ?";
    $params = [$hotelId];

    if ($status !== '') { $sql .= " AND r.status = ?"; $params[] = $status; }
    if ($floor !== '')  { $sql .= " AND r.floor = ?";  $params[] = (int) $floor; }
    if ($typeId !== '') { $sql .= " AND r.type_id = ?"; $params[] = (int) $typeId; }

    $sql .= " ORDER BY r.number ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();

    // Floors + types for filters
    $stmt = $this->db->prepare("SELECT DISTINCT floor FROM rooms WHERE hotel_id = ? ORDER BY floor");
    $stmt->execute([$hotelId]);
    $floors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $this->db->prepare("SELECT * FROM room_types WHERE hotel_id = ? ORDER BY name");
    $stmt->execute([$hotelId]);
    $types = $stmt->fetchAll();

    // Room status overview counts
    $stmt = $this->db->prepare("SELECT status, COUNT(*) as cnt FROM rooms WHERE hotel_id = ? GROUP BY status");
    $stmt->execute([$hotelId]);
    $statusCounts = [];
    foreach ($stmt->fetchAll() as $row) $statusCounts[$row['status']] = (int) $row['cnt'];

    $title = 'Chambres — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /rooms/create
  public function create() {
    $hotelId = current_hotel_id();
    $errors = [];

    $types = $this->getTypes($hotelId);

    $form = [
      'number'  => '',
      'type_id' => '',
      'floor'   => '1',
      'price'   => '0',
      'status'  => 'available',
      'notes'   => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'number'  => trim((string) input('number', '')),
        'type_id' => (string) input('type_id', ''),
        'floor'   => (string) input('floor', '1'),
        'price'   => (string) input('price', '0'),
        'status'  => (string) input('status', 'available'),
        'notes'   => (string) input('notes', ''),
      ];

      if ($form['number'] === '') $errors[] = 'Le numéro de chambre est requis.';

      // Unique number within hotel
      if (empty($errors)) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND number = ?");
        $stmt->execute([$hotelId, $form['number']]);
        if ((int) $stmt->fetchColumn() > 0) {
          $errors[] = 'Ce numéro de chambre existe déjà.';
        }
      }

      if (empty($errors)) {
        $qrToken = generate_qr_token();
        $stmt = $this->db->prepare(
          "INSERT INTO rooms (hotel_id, number, type_id, floor, price, status, qr_token, notes)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
          $hotelId,
          $form['number'],
          $form['type_id'] !== '' ? (int) $form['type_id'] : null,
          (int) $form['floor'],
          (float) $form['price'],
          $form['status'],
          $qrToken,
          $form['notes'],
        ]);
        $roomId = (int) $this->db->lastInsertId();
        redirect('/rooms/qrcode/' . $roomId);
      }
    }

    $statuses = $this->statuses();

    $title = 'Nouvelle chambre — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /rooms/edit/{id}
  public function edit(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      die('Chambre introuvable.');
    }

    $errors = [];
    $types = $this->getTypes($hotelId);

    $form = [
      'number'  => $room['number'],
      'type_id' => $room['type_id'] !== null ? (string) $room['type_id'] : '',
      'floor'   => (string) $room['floor'],
      'price'   => (string) $room['price'],
      'status'  => $room['status'],
      'notes'   => $room['notes'] ?? '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'number'  => trim((string) input('number', '')),
        'type_id' => (string) input('type_id', ''),
        'floor'   => (string) input('floor', '1'),
        'price'   => (string) input('price', '0'),
        'status'  => (string) input('status', 'available'),
        'notes'   => (string) input('notes', ''),
      ];

      if ($form['number'] === '') $errors[] = 'Le numéro de chambre est requis.';

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND number = ? AND id != ?"
        );
        $stmt->execute([$hotelId, $form['number'], $id]);
        if ((int) $stmt->fetchColumn() > 0) {
          $errors[] = 'Ce numéro de chambre existe déjà.';
        }
      }

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "UPDATE rooms
           SET number = ?, type_id = ?, floor = ?, price = ?, status = ?, notes = ?
           WHERE id = ? AND hotel_id = ?"
        );
        $stmt->execute([
          $form['number'],
          $form['type_id'] !== '' ? (int) $form['type_id'] : null,
          (int) $form['floor'],
          (float) $form['price'],
          $form['status'],
          $form['notes'],
          $id, $hotelId,
        ]);
        redirect('/rooms');
      }
    }

    $statuses = $this->statuses();

    $title = 'Modifier chambre — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/edit.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /rooms/qrcode/{id}
  public function qrcode(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare(
      "SELECT r.*, rt.name as type_name, h.name as hotel_name
       FROM rooms r
       LEFT JOIN room_types rt ON r.type_id = rt.id
       JOIN hotels h ON r.hotel_id = h.id
       WHERE r.id = ? AND r.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      die('Chambre introuvable.');
    }

    // Generate QR code URL via Google Chart API compatible service
    $qrData = APP_URL . '/room/' . $room['qr_token'];
    $qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrData);

    $title = 'QR Code — Chambre ' . $room['number'] . ' — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/qrcode.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /rooms/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare(
      "SELECT r.*, rt.name as type_name FROM rooms r
       LEFT JOIN room_types rt ON r.type_id = rt.id
       WHERE r.id = ? AND r.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      die('Chambre introuvable.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }
      $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = ?");
      $stmt->execute([$id]);
      redirect('/rooms');
    }

    $title = 'Supprimer chambre — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/delete.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // =====================================================
  // ROOM TYPES (embedded CRUD)
  // =====================================================

  // GET /rooms/types
  public function types() {
    $hotelId = current_hotel_id();
    $stmt = $this->db->prepare(
      "SELECT rt.*, (SELECT COUNT(*) FROM rooms r WHERE r.type_id = rt.id AND r.hotel_id = ?) as rooms_count
       FROM room_types rt
       WHERE rt.hotel_id = ?
       ORDER BY rt.name"
    );
    $stmt->execute([$hotelId, $hotelId]);
    $types = $stmt->fetchAll();

    $title = 'Types de chambres — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/types.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /rooms/typesCreate
  public function typesCreate() {
    $hotelId = current_hotel_id();
    $errors = [];

    $form = ['name' => '', 'description' => '', 'base_price' => '0', 'capacity' => '2', 'amenities' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'name'        => trim((string) input('name', '')),
        'description' => (string) input('description', ''),
        'base_price'  => (string) input('base_price', '0'),
        'capacity'    => (string) input('capacity', '2'),
        'amenities'   => trim((string) input('amenities', '')),
      ];

      if ($form['name'] === '') $errors[] = 'Le nom du type est requis.';

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "INSERT INTO room_types (hotel_id, name, description, base_price, capacity, amenities)
           VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
          $hotelId,
          $form['name'],
          $form['description'],
          (float) $form['base_price'],
          (int) $form['capacity'],
          $form['amenities'],
        ]);
        redirect('/rooms/types');
      }
    }

    $formAction = '/rooms/typesCreate';
    $isEdit = false;

    $title = 'Nouveau type de chambre — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/types_form.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /rooms/typesEdit/{id}
  public function typesEdit(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM room_types WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $type = $stmt->fetch();

    if (!$type) {
      http_response_code(404);
      die('Type introuvable.');
    }

    $errors = [];

    $form = [
      'name'        => $type['name'],
      'description' => $type['description'] ?? '',
      'base_price'  => (string) $type['base_price'],
      'capacity'    => (string) $type['capacity'],
      'amenities'   => $type['amenities'] ?? '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'name'        => trim((string) input('name', '')),
        'description' => (string) input('description', ''),
        'base_price'  => (string) input('base_price', '0'),
        'capacity'    => (string) input('capacity', '2'),
        'amenities'   => trim((string) input('amenities', '')),
      ];

      if ($form['name'] === '') $errors[] = 'Le nom du type est requis.';

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "UPDATE room_types
           SET name = ?, description = ?, base_price = ?, capacity = ?, amenities = ?
           WHERE id = ?"
        );
        $stmt->execute([
          $form['name'], $form['description'],
          (float) $form['base_price'], (int) $form['capacity'], $form['amenities'],
          $id,
        ]);
        redirect('/rooms/types');
      }
    }

    $formAction = '/rooms/typesEdit/' . $id;
    $isEdit = true;

    $title = 'Modifier type de chambre — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/rooms/types_form.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /rooms/typesDelete/{id}
  public function typesDelete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    if (!verify_csrf()) {
      http_response_code(419);
      die('Token de sécurité invalide.');
    }

    $stmt = $this->db->prepare("DELETE FROM room_types WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    redirect('/rooms/types');
  }

  // =====================================================
  // helpers
  // =====================================================
  private function getTypes(int $hotelId): array {
    $stmt = $this->db->prepare("SELECT * FROM room_types WHERE hotel_id = ? ORDER BY name");
    $stmt->execute([$hotelId]);
    return $stmt->fetchAll();
  }

  private function statuses(): array {
    return [
      'available'    => 'Disponible',
      'occupied'     => 'Occupée',
      'cleaning'     => 'En nettoyage',
      'maintenance'  => 'Maintenance',
      'out_of_order' => 'Hors service',
    ];
  }
}
