<?php
// =============================================
// RoomScan — Guests Controller
// =============================================

class GuestsController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /guests
  public function index() {
    $hotelId = current_hotel_id();

    $search = trim((string) input('q', ''));
    $vipOnly = input('vip', '') === '1';

    $sql = "SELECT g.*,
                   (SELECT COUNT(*) FROM reservations r WHERE r.guest_id = g.id AND r.status NOT IN ('cancelled','no_show')) as stays_count
            FROM guests g
            WHERE g.hotel_id = ?";
    $params = [$hotelId];

    if ($search !== '') {
      $sql .= " AND (g.name LIKE ? OR g.email LIKE ? OR g.phone LIKE ?)";
      $params[] = '%' . $search . '%';
      $params[] = '%' . $search . '%';
      $params[] = '%' . $search . '%';
    }
    if ($vipOnly) {
      $sql .= " AND g.vip = 1";
    }
    $sql .= " ORDER BY g.name ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $guests = $stmt->fetchAll();

    $title = 'Clients — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/guests/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /guests/create
  public function create() {
    $hotelId = current_hotel_id();
    $errors = [];

    $form = [
      'name'        => '',
      'email'       => '',
      'phone'       => '',
      'country'     => '',
      'id_type'     => '',
      'id_number'   => '',
      'vip'         => false,
      'preferences' => '',
      'notes'       => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'name'        => trim((string) input('name', '')),
        'email'       => trim((string) input('email', '')),
        'phone'       => trim((string) input('phone', '')),
        'country'     => trim((string) input('country', '')),
        'id_type'     => (string) input('id_type', ''),
        'id_number'   => trim((string) input('id_number', '')),
        'vip'         => input('vip', '0') === '1',
        'preferences' => (string) input('preferences', ''),
        'notes'       => (string) input('notes', ''),
      ];

      if ($form['name'] === '') $errors[] = 'Le nom du client est requis.';

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "INSERT INTO guests
            (hotel_id, name, email, phone, country, id_type, id_number, vip, preferences, notes, created_at)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
          $hotelId,
          $form['name'],
          $form['email'],
          $form['phone'],
          $form['country'],
          $form['id_type'],
          $form['id_number'],
          $form['vip'] ? 1 : 0,
          $form['preferences'],
          $form['notes'],
        ]);
        $guestId = (int) $this->db->lastInsertId();
        redirect('/guests/view/' . $guestId);
      }
    }

    $idTypes = [
      ''                => '— Aucun —',
      'cni'             => 'CNI',
      'passport'        => 'Passeport',
      'driver_license'  => 'Permis de conduire',
      'residence_card'  => 'Carte de séjour',
    ];
    $countries = ['Sénégal','Côte d\'Ivoire','Mali','Guinée','France','Maroc','Tunisie','Algérie','Cameroun','Burkina Faso','Ghana','Nigeria','Belgique','Canada','États-Unis','Autre'];

    $title = 'Nouveau client — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/guests/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /guests/edit/{id}
  public function edit(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM guests WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $guest = $stmt->fetch();

    if (!$guest) {
      http_response_code(404);
      die('Client introuvable.');
    }

    $errors = [];

    $form = [
      'name'        => $guest['name'],
      'email'       => $guest['email'] ?? '',
      'phone'       => $guest['phone'] ?? '',
      'country'     => $guest['country'] ?? '',
      'id_type'     => $guest['id_type'] ?? '',
      'id_number'   => $guest['id_number'] ?? '',
      'vip'         => (bool) $guest['vip'],
      'preferences' => $guest['preferences'] ?? '',
      'notes'       => $guest['notes'] ?? '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'name'        => trim((string) input('name', '')),
        'email'       => trim((string) input('email', '')),
        'phone'       => trim((string) input('phone', '')),
        'country'     => trim((string) input('country', '')),
        'id_type'     => (string) input('id_type', ''),
        'id_number'   => trim((string) input('id_number', '')),
        'vip'         => input('vip', '0') === '1',
        'preferences' => (string) input('preferences', ''),
        'notes'       => (string) input('notes', ''),
      ];

      if ($form['name'] === '') $errors[] = 'Le nom du client est requis.';

      if (empty($errors)) {
        $stmt = $this->db->prepare(
          "UPDATE guests
           SET name = ?, email = ?, phone = ?, country = ?, id_type = ?, id_number = ?,
               vip = ?, preferences = ?, notes = ?
           WHERE id = ?"
        );
        $stmt->execute([
          $form['name'], $form['email'], $form['phone'], $form['country'],
          $form['id_type'], $form['id_number'],
          $form['vip'] ? 1 : 0,
          $form['preferences'], $form['notes'],
          $id,
        ]);
        redirect('/guests/view/' . $id);
      }
    }

    $idTypes = [
      ''                => '— Aucun —',
      'cni'             => 'CNI',
      'passport'        => 'Passeport',
      'driver_license'  => 'Permis de conduire',
      'residence_card'  => 'Carte de séjour',
    ];
    $countries = ['Sénégal','Côte d\'Ivoire','Mali','Guinée','France','Maroc','Tunisie','Algérie','Cameroun','Burkina Faso','Ghana','Nigeria','Belgique','Canada','États-Unis','Autre'];

    $title = 'Modifier client — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/guests/edit.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /guests/view/{id}
  public function view(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM guests WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $guest = $stmt->fetch();

    if (!$guest) {
      http_response_code(404);
      die('Client introuvable.');
    }

    // Reservation history
    $stmt = $this->db->prepare(
      "SELECT r.*, rm.number as room_number
       FROM reservations r
       JOIN rooms rm ON r.room_id = rm.id
       WHERE r.guest_id = ?
       ORDER BY r.check_in DESC"
    );
    $stmt->execute([$id]);
    $reservations = $stmt->fetchAll();

    $totalSpent = (float) $guest['total_spent'];
    $totalStays = (int) $guest['total_stays'];

    $idTypes = [
      'cni'             => 'CNI',
      'passport'        => 'Passeport',
      'driver_license'  => 'Permis de conduire',
      'residence_card'  => 'Carte de séjour',
    ];

    $title = $guest['name'] . ' — Client — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/guests/view.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /guests/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM guests WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $guest = $stmt->fetch();

    if (!$guest) {
      http_response_code(404);
      die('Client introuvable.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }
      $stmt = $this->db->prepare("DELETE FROM guests WHERE id = ?");
      $stmt->execute([$id]);
      redirect('/guests');
    }

    $title = 'Supprimer client — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/guests/delete.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }
}
