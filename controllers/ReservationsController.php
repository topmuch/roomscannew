<?php
// =============================================
// RoomScan — Reservations Controller
// =============================================

class ReservationsController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /reservations
  public function index() {
    $hotelId = current_hotel_id();

    $status = trim((string) input('status', ''));
    $search = trim((string) input('q', ''));

    $sql = "SELECT r.*, g.name as guest_name, g.phone as guest_phone, g.vip as guest_vip,
                   rm.number as room_number, rm.floor as room_floor
            FROM reservations r
            JOIN guests g ON r.guest_id = g.id
            JOIN rooms rm ON r.room_id = rm.id
            WHERE r.hotel_id = ?";
    $params = [$hotelId];

    if ($status !== '') {
      $sql .= " AND r.status = ?";
      $params[] = $status;
    }
    if ($search !== '') {
      $sql .= " AND g.name LIKE ?";
      $params[] = '%' . $search . '%';
    }
    $sql .= " ORDER BY r.check_in DESC, r.id DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();

    $statusCounts = $this->statusCounts($hotelId);

    $title = 'Réservations — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/reservations/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /reservations/create
  public function create() {
    $hotelId = current_hotel_id();
    $errors = [];

    // Guests for select
    $stmt = $this->db->prepare("SELECT * FROM guests WHERE hotel_id = ? ORDER BY name");
    $stmt->execute([$hotelId]);
    $guests = $stmt->fetchAll();

    // Rooms for select (with type name)
    $stmt = $this->db->prepare(
      "SELECT r.*, rt.name as type_name, rt.base_price as type_base_price
       FROM rooms r
       LEFT JOIN room_types rt ON r.type_id = rt.id
       WHERE r.hotel_id = ? ORDER BY r.number"
    );
    $stmt->execute([$hotelId]);
    $rooms = $stmt->fetchAll();

    // Pre-fill from query string (e.g. /reservations/create?room_id=5)
    $form = [
      'guest_id'        => (int) input('guest_id', 0),
      'room_id'         => (int) input('room_id', 0),
      'check_in'        => (string) input('check_in', date('Y-m-d')),
      'check_out'       => (string) input('check_out', date('Y-m-d', strtotime('+1 day'))),
      'adults'          => (int) input('adults', 1),
      'children'        => (int) input('children', 0),
      'room_price'      => (float) input('room_price', 0),
      'source'          => (string) input('source', 'direct'),
      'notes'           => '',
      'new_guest'       => false,
      'guest_name'      => '',
      'guest_email'     => '',
      'guest_phone'     => '',
      'guest_country'   => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = array_merge($form, [
        'guest_id'      => (int) input('guest_id', 0),
        'room_id'       => (int) input('room_id', 0),
        'check_in'      => (string) input('check_in', ''),
        'check_out'     => (string) input('check_out', ''),
        'adults'        => max(1, (int) input('adults', 1)),
        'children'      => max(0, (int) input('children', 0)),
        'room_price'    => (float) input('room_price', 0),
        'source'        => (string) input('source', 'direct'),
        'notes'         => (string) input('notes', ''),
        'new_guest'     => input('new_guest', '0') === '1',
        'guest_name'    => trim((string) input('guest_name', '')),
        'guest_email'   => trim((string) input('guest_email', '')),
        'guest_phone'   => trim((string) input('guest_phone', '')),
        'guest_country' => trim((string) input('guest_country', '')),
      ]);

      // Guest resolution
      $guestId = 0;
      if ($form['new_guest']) {
        if ($form['guest_name'] === '') {
          $errors[] = 'Le nom du client est requis.';
        } else {
          $stmt = $this->db->prepare(
            "INSERT INTO guests (hotel_id, name, email, phone, country, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
          );
          $stmt->execute([
            $hotelId,
            $form['guest_name'],
            $form['guest_email'],
            $form['guest_phone'],
            $form['guest_country'],
          ]);
          $guestId = (int) $this->db->lastInsertId();
        }
      } else {
        $guestId = $form['guest_id'];
        if ($guestId <= 0) $errors[] = 'Sélectionnez un client.';
      }

      // Room
      if ($form['room_id'] <= 0) $errors[] = 'Sélectionnez une chambre.';

      // Dates
      if ($form['check_in'] === '' || $form['check_out'] === '') {
        $errors[] = 'Les dates d\'arrivée et de départ sont requises.';
      } elseif ($form['check_in'] >= $form['check_out']) {
        $errors[] = 'La date de départ doit être après la date d\'arrivée.';
      }

      // Room availability
      if ($form['room_id'] > 0 && $form['check_in'] && $form['check_out'] && empty($errors)) {
        $stmt = $this->db->prepare(
          "SELECT COUNT(*) FROM reservations
           WHERE room_id = ? AND hotel_id = ?
           AND status NOT IN ('cancelled','checked_out','no_show')
           AND check_in < ? AND check_out > ?"
        );
        $stmt->execute([
          $form['room_id'], $hotelId, $form['check_out'], $form['check_in']
        ]);
        if ((int) $stmt->fetchColumn() > 0) {
          $errors[] = 'Chambre non disponible pour ces dates.';
        }
      }

      if (empty($errors)) {
        $nights = days_between($form['check_in'], $form['check_out']);
        $total  = $form['room_price'] * $nights;

        $stmt = $this->db->prepare(
          "INSERT INTO reservations
            (hotel_id, guest_id, room_id, check_in, check_out, adults, children,
             status, room_price, total, source, notes, created_by, created_at)
           VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
          $hotelId, $guestId, $form['room_id'],
          $form['check_in'], $form['check_out'],
          $form['adults'], $form['children'],
          $form['room_price'], $total,
          $form['source'], $form['notes'],
          current_user()['id'] ?? null,
        ]);
        $reservationId = (int) $this->db->lastInsertId();

        // Update guest stats
        $stmt = $this->db->prepare(
          "UPDATE guests SET total_stays = total_stays + 1, total_spent = total_spent + ? WHERE id = ?"
        );
        $stmt->execute([$total, $guestId]);

        redirect('/reservations/view/' . $reservationId);
      }
    }

    $sources = [
      'direct'      => 'Direct',
      'walk_in'     => 'Walk-in',
      'booking.com' => 'Booking.com',
      'airbnb'      => 'Airbnb',
      'expedia'     => 'Expedia',
      'other'       => 'Autre',
    ];

    $title = 'Nouvelle réservation — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/reservations/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /reservations/edit/{id}
  public function edit(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
      http_response_code(404);
      die('Réservation introuvable.');
    }

    $errors = [];

    // Guests and rooms for select
    $stmt = $this->db->prepare("SELECT * FROM guests WHERE hotel_id = ? ORDER BY name");
    $stmt->execute([$hotelId]);
    $guests = $stmt->fetchAll();

    $stmt = $this->db->prepare(
      "SELECT r.*, rt.name as type_name FROM rooms r
       LEFT JOIN room_types rt ON r.type_id = rt.id
       WHERE r.hotel_id = ? ORDER BY r.number"
    );
    $stmt->execute([$hotelId]);
    $rooms = $stmt->fetchAll();

    $form = [
      'guest_id'   => (int) $reservation['guest_id'],
      'room_id'    => (int) $reservation['room_id'],
      'check_in'   => $reservation['check_in'],
      'check_out'  => $reservation['check_out'],
      'adults'     => (int) $reservation['adults'],
      'children'   => (int) $reservation['children'],
      'room_price' => (float) $reservation['room_price'],
      'source'     => $reservation['source'],
      'status'     => $reservation['status'],
      'notes'      => $reservation['notes'] ?? '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }

      $form = [
        'guest_id'   => (int) input('guest_id', 0),
        'room_id'    => (int) input('room_id', 0),
        'check_in'   => (string) input('check_in', ''),
        'check_out'  => (string) input('check_out', ''),
        'adults'     => max(1, (int) input('adults', 1)),
        'children'   => max(0, (int) input('children', 0)),
        'room_price' => (float) input('room_price', 0),
        'source'     => (string) input('source', 'direct'),
        'status'     => (string) input('status', 'confirmed'),
        'notes'      => (string) input('notes', ''),
      ];

      if ($form['guest_id'] <= 0) $errors[] = 'Sélectionnez un client.';
      if ($form['room_id'] <= 0)  $errors[] = 'Sélectionnez une chambre.';
      if ($form['check_in'] === '' || $form['check_out'] === '') {
        $errors[] = 'Les dates sont requises.';
      } elseif ($form['check_in'] >= $form['check_out']) {
        $errors[] = 'La date de départ doit être après la date d\'arrivée.';
      }

      // Availability check (excluding this reservation)
      if ($form['room_id'] > 0 && $form['check_in'] && $form['check_out'] && empty($errors)) {
        $stmt = $this->db->prepare(
          "SELECT COUNT(*) FROM reservations
           WHERE room_id = ? AND hotel_id = ? AND id != ?
           AND status NOT IN ('cancelled','checked_out','no_show')
           AND check_in < ? AND check_out > ?"
        );
        $stmt->execute([
          $form['room_id'], $hotelId, $id,
          $form['check_out'], $form['check_in']
        ]);
        if ((int) $stmt->fetchColumn() > 0) {
          $errors[] = 'Chambre non disponible pour ces dates.';
        }
      }

      if (empty($errors)) {
        $nights = days_between($form['check_in'], $form['check_out']);
        $total  = $form['room_price'] * $nights;

        $stmt = $this->db->prepare(
          "UPDATE reservations
           SET guest_id = ?, room_id = ?, check_in = ?, check_out = ?,
               adults = ?, children = ?, room_price = ?, total = ?,
               source = ?, status = ?, notes = ?
           WHERE id = ?"
        );
        $stmt->execute([
          $form['guest_id'], $form['room_id'], $form['check_in'], $form['check_out'],
          $form['adults'], $form['children'], $form['room_price'], $total,
          $form['source'], $form['status'], $form['notes'], $id,
        ]);

        // Update room status if becoming checked_in
        if ($form['status'] === 'checked_in') {
          $stmt = $this->db->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ? AND hotel_id = ?");
          $stmt->execute([$form['room_id'], $hotelId]);
        }

        redirect('/reservations/view/' . $id);
      }
    }

    $sources = [
      'direct'      => 'Direct',
      'walk_in'     => 'Walk-in',
      'booking.com' => 'Booking.com',
      'airbnb'      => 'Airbnb',
      'expedia'     => 'Expedia',
      'other'       => 'Autre',
    ];
    $statuses = [
      'pending'     => 'En attente',
      'confirmed'   => 'Confirmée',
      'checked_in'  => 'Arrivé',
      'checked_out' => 'Parti',
      'cancelled'   => 'Annulée',
      'no_show'     => 'No-show',
    ];

    $title = 'Modifier réservation — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/reservations/edit.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /reservations/view/{id}
  public function view(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare(
      "SELECT r.*,
              g.name as guest_name, g.email as guest_email, g.phone as guest_phone,
              g.country as guest_country, g.vip as guest_vip, g.id_number as guest_id_number,
              g.id_type as guest_id_type, g.preferences as guest_preferences,
              rm.number as room_number, rm.floor as room_floor, rm.status as room_status,
              rt.name as room_type_name, rt.capacity as room_capacity, rt.amenities as room_amenities
       FROM reservations r
       JOIN guests g ON r.guest_id = g.id
       JOIN rooms rm ON r.room_id = rm.id
       LEFT JOIN room_types rt ON rm.type_id = rt.id
       WHERE r.id = ? AND r.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
      http_response_code(404);
      die('Réservation introuvable.');
    }

    // Room service charges linked to this reservation
    $stmt = $this->db->prepare(
      "SELECT * FROM room_service_orders
       WHERE reservation_id = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$id]);
    $charges = $stmt->fetchAll();

    $extrasTotal = 0;
    foreach ($charges as $c) $extrasTotal += (float) $c['total'];

    $nights      = days_between($reservation['check_in'], $reservation['check_out']);
    $roomTotal   = (float) $reservation['room_price'] * $nights;
    $grandTotal  = $roomTotal + $extrasTotal;

    $title = 'Réservation #' . $id . ' — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/reservations/view.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /reservations/checkin/{id}
  public function checkin(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    if (!verify_csrf()) {
      http_response_code(419);
      die('Token de sécurité invalide.');
    }

    $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
      http_response_code(404);
      die('Réservation introuvable.');
    }

    $stmt = $this->db->prepare("UPDATE reservations SET status = 'checked_in' WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $this->db->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$reservation['room_id'], $hotelId]);

    redirect('/reservations/view/' . $id);
  }

  // POST /reservations/checkout/{id}
  public function checkout(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    if (!verify_csrf()) {
      http_response_code(419);
      die('Token de sécurité invalide.');
    }

    $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
      http_response_code(404);
      die('Réservation introuvable.');
    }

    $stmt = $this->db->prepare("UPDATE reservations SET status = 'checked_out' WHERE id = ?");
    $stmt->execute([$id]);

    // Room goes to cleaning
    $stmt = $this->db->prepare("UPDATE rooms SET status = 'cleaning' WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$reservation['room_id'], $hotelId]);

    // Note: invoice generation deferred — flagged via reservation status only
    redirect('/reservations/view/' . $id);
  }

  // GET/POST /reservations/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;

    $stmt = $this->db->prepare(
      "SELECT r.*, g.name as guest_name, rm.number as room_number
       FROM reservations r
       JOIN guests g ON r.guest_id = g.id
       JOIN rooms rm ON r.room_id = rm.id
       WHERE r.id = ? AND r.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
      http_response_code(404);
      die('Réservation introuvable.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        http_response_code(419);
        die('Token de sécurité invalide.');
      }
      $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
      $stmt->execute([$id]);
      redirect('/reservations');
    }

    $title = 'Supprimer réservation — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/reservations/delete.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // ---- helpers ----
  private function statusCounts(int $hotelId): array {
    $stmt = $this->db->prepare(
      "SELECT status, COUNT(*) as cnt FROM reservations WHERE hotel_id = ? GROUP BY status"
    );
    $stmt->execute([$hotelId]);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
      $out[$row['status']] = (int) $row['cnt'];
    }
    return $out;
  }
}
