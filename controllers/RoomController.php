<?php
// =============================================
// RoomScan — Room Controller (QR Code public page)
// =============================================

class RoomController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    // This is a PUBLIC controller — no auth needed
  }

  // GET /room/{qr_token}
  public function index(?string $token = null) {
    if (!$token) {
      http_response_code(404);
      echo '<h1>QR code invalide</h1>';
      return;
    }

    // Find room by qr_token
    $stmt = $this->db->prepare(
      'SELECT r.*, h.name as hotel_name, h.wifi_ssid, h.wifi_password,
              h.checkin_time, h.checkout_time, h.phone as hotel_phone
       FROM rooms r
       JOIN hotels h ON r.hotel_id = h.id
       WHERE r.qr_token = ? AND h.status = "active"'
    );
    $stmt->execute([$token]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      echo '<h1>Chambre introuvable</h1>';
      return;
    }

    // Get available menu items
    $stmt = $this->db->prepare(
      'SELECT * FROM menu_items WHERE hotel_id = ? AND available = 1 ORDER BY category, name'
    );
    $stmt->execute([$room['hotel_id']]);
    $menuItems = $stmt->fetchAll();

    // Group by category
    $menuByCategory = [];
    foreach ($menuItems as $item) {
      $cat = $item['category'] ?? 'Autre';
      $menuByCategory[$cat][] = $item;
    }

    // Handle POST (room service order or service request)
    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = input('action', '');

      if ($action === 'order') {
        // Room service order
        $items = json_decode(input('items', '[]'), true);
        if (empty($items)) {
          $error = 'Sélectionnez au moins un article.';
        } else {
          $total = 0;
          foreach ($items as $item) {
            $total += $item['price'] * $item['qty'];
          }
          $stmt = $this->db->prepare(
            'INSERT INTO room_service_orders (hotel_id, room_id, items, total, status, guest_name, guest_phone, notes)
             VALUES (?, ?, ?, ?, "pending", ?, ?, ?)'
          );
          $stmt->execute([
            $room['hotel_id'],
            $room['id'],
            json_encode($items),
            $total,
            input('guest_name', ''),
            input('guest_phone', ''),
            input('notes', ''),
          ]);
          $success = 'Commande envoyée ! La réception vous confirmera sous peu.';
        }
      } elseif ($action === 'service') {
        // Service request
        $type = input('service_type', '');
        $validTypes = ['housekeeping', 'towels', 'maintenance', 'reception', 'extra_pillow', 'wake_up'];
        if (!in_array($type, $validTypes)) {
          $error = 'Type de service invalide.';
        } else {
          $stmt = $this->db->prepare(
            'INSERT INTO service_requests (hotel_id, room_id, type, status, notes)
             VALUES (?, ?, ?, "pending", ?)'
          );
          $stmt->execute([
            $room['hotel_id'],
            $room['id'],
            $type,
            input('notes', ''),
          ]);
          $success = 'Demande envoyée ! L\'équipe vous répondra rapidement.';
        }
      } elseif ($action === 'lostfound') {
        // Lost & found report
        $stmt = $this->db->prepare(
          'INSERT INTO lost_found (hotel_id, room_id, type, item_name, description, reported_by, contact, notes)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
          $room['hotel_id'],
          $room['id'],
          input('lf_type', 'lost'),
          input('item_name', ''),
          input('description', ''),
          input('reported_by', ''),
          input('contact', ''),
          input('notes', ''),
        ]);
        $success = 'Signalement enregistré. L\'équipe vous contactera si l\'objet est retrouvé.';
      }
    }

    $title = 'Chambre ' . $room['number'] . ' — ' . $room['hotel_name'];
    ob_start();
    require __DIR__ . '/../views/room/qr_page.php';
    echo ob_get_clean();
  }

  // =============================================
  // GET /clean/{qr_token} — Housekeeping QR scan page
  // Femme de ménage scanne le QR code de la chambre
  // =============================================
  public function clean(?string $token = null) {
    if (!$token) {
      http_response_code(404);
      echo '<h1>QR code invalide</h1>';
      return;
    }

    // Find room by qr_token
    $stmt = $this->db->prepare(
      'SELECT r.*, h.name as hotel_name
       FROM rooms r
       JOIN hotels h ON r.hotel_id = h.id
       WHERE r.qr_token = ? AND h.status = "active"'
    );
    $stmt->execute([$token]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      echo '<h1>Chambre introuvable</h1>';
      return;
    }

    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = input('action', '');

      if ($action === 'start_cleaning') {
        // Create housekeeping task + set room to cleaning
        $checklist = json_encode([
          'bed_made' => false,
          'towels_changed' => false,
          'floor_cleaned' => false,
          'bathroom_cleaned' => false,
          'trash_emptied' => false,
          'dusting_done' => false,
          'windows_cleaned' => false,
          'amenities_replaced' => false,
        ]);

        $stmt = $this->db->prepare(
          'INSERT INTO housekeeping (hotel_id, room_id, status, priority, checklist, requested_at, started_at)
           VALUES (?, ?, "in_progress", "normal", ?, NOW(), NOW())'
        );
        $stmt->execute([$room['hotel_id'], $room['id'], $checklist]);
        $hkId = (int) $this->db->lastInsertId();

        // Update room status
        $stmt = $this->db->prepare('UPDATE rooms SET status = "cleaning" WHERE id = ?');
        $stmt->execute([$room['id']]);

        $success = 'Nettoyage démarré pour la chambre N°' . $room['number'];
        // Redirect to checklist page
        redirect('/clean/' . $token . '?task=' . $hkId);

      } elseif ($action === 'submit_checklist') {
        $hkId = (int) input('hk_id', '0');

        // Build checklist from POST
        $checklist = [
          'bed_made' => isset($_POST['bed_made']),
          'towels_changed' => isset($_POST['towels_changed']),
          'floor_cleaned' => isset($_POST['floor_cleaned']),
          'bathroom_cleaned' => isset($_POST['bathroom_cleaned']),
          'trash_emptied' => isset($_POST['trash_emptied']),
          'dusting_done' => isset($_POST['dusting_done']),
          'windows_cleaned' => isset($_POST['windows_cleaned']),
          'amenities_replaced' => isset($_POST['amenities_replaced']),
        ];

        $allDone = !in_array(false, $checklist);
        $flaggedIssue = trim(input('flagged_issue', ''));
        $notes = trim(input('notes', ''));

        $status = $allDone ? 'completed' : 'in_progress';
        if (!$allDone) $status = 'flagged';

        $stmt = $this->db->prepare(
          'UPDATE housekeeping SET checklist = ?, status = ?, flagged_issue = ?, notes = ?, completed_at = IF(? = "completed", NOW(), NULL)
           WHERE id = ? AND room_id = ?'
        );
        $stmt->execute([json_encode($checklist), $status, $flaggedIssue, $notes, $status, $hkId, $room['id']]);

        if ($status === 'completed') {
          // Set room to available
          $stmt = $this->db->prepare('UPDATE rooms SET status = "available" WHERE id = ?');
          $stmt->execute([$room['id']]);
          $success = '✅ Chambre N°' . $room['number'] . ' nettoyée et disponible !';
        } else {
          $success = '⚠️ Checklist partielle enregistrée. Problème signalé : ' . ($flaggedIssue ?: 'aucun');
        }
      } elseif ($action === 'flag_maintenance') {
        // Flag room for maintenance
        $issue = trim(input('issue', ''));
        $stmt = $this->db->prepare('UPDATE rooms SET status = "maintenance" WHERE id = ?');
        $stmt->execute([$room['id']]);

        // Create service request
        $stmt = $this->db->prepare(
          'INSERT INTO service_requests (hotel_id, room_id, type, status, priority, notes)
           VALUES (?, ?, "maintenance", "pending", "high", ?)'
        );
        $stmt->execute([$room['hotel_id'], $room['id'], $issue]);
        $success = '🔧 Problème signalé. Maintenance notifiée.';
      }
    }

    // Check if there's an active housekeeping task
    $activeTask = null;
    $taskId = $_GET['task'] ?? null;
    if ($taskId) {
      $stmt = $this->db->prepare('SELECT * FROM housekeeping WHERE id = ? AND room_id = ?');
      $stmt->execute([$taskId, $room['id']]);
      $activeTask = $stmt->fetch();
    } else {
      // Check for in_progress task
      $stmt = $this->db->prepare('SELECT * FROM housekeeping WHERE room_id = ? AND status = "in_progress" ORDER BY requested_at DESC LIMIT 1');
      $stmt->execute([$room['id']]);
      $activeTask = $stmt->fetch();
    }

    $title = 'Nettoyage Chambre ' . $room['number'] . ' — ' . $room['hotel_name'];
    ob_start();
    require __DIR__ . '/../views/room/clean.php';
    echo ob_get_clean();
  }

  // =============================================
  // GET /found/{qr_token} — Lost & Found QR scan page
  // Scanner un QR code pour signaler un objet trouvé/perdu
  // =============================================
  public function found(?string $token = null) {
    if (!$token) {
      http_response_code(404);
      echo '<h1>QR code invalide</h1>';
      return;
    }

    // Find room by qr_token
    $stmt = $this->db->prepare(
      'SELECT r.*, h.name as hotel_name
       FROM rooms r
       JOIN hotels h ON r.hotel_id = h.id
       WHERE r.qr_token = ? AND h.status = "active"'
    );
    $stmt->execute([$token]);
    $room = $stmt->fetch();

    if (!$room) {
      http_response_code(404);
      echo '<h1>Chambre introuvable</h1>';
      return;
    }

    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $itemName = trim(input('item_name', ''));
      if (!$itemName) {
        $error = 'Veuillez indiquer le nom de l\'objet.';
      } else {
        // Handle photo upload
        $photoPath = '';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
          $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
          $allowed = ['jpg', 'jpeg', 'png', 'webp'];
          if (in_array($ext, $allowed) && $_FILES['photo']['size'] < 2 * 1024 * 1024) {
            $photoPath = '/public/uploads/lostfound/' . uniqid('lf_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/..' . $photoPath);
          }
        }

        $stmt = $this->db->prepare(
          'INSERT INTO lost_found (hotel_id, room_id, type, item_name, description, location, photo, reported_by, contact, notes)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
          $room['hotel_id'],
          $room['id'],
          input('lf_type', 'found'),
          $itemName,
          input('description', ''),
          input('location', 'Chambre ' . $room['number']),
          $photoPath,
          input('reported_by', ''),
          input('contact', ''),
          input('notes', ''),
        ]);
        $success = input('lf_type', 'found') === 'lost'
          ? 'Objet perdu signalé. L\'équipe vous contactera si l\'objet est retrouvé.'
          : 'Objet trouvé enregistré. Merci !';
      }
    }

    // Show recent items for this room
    $stmt = $this->db->prepare(
      'SELECT * FROM lost_found WHERE room_id = ? ORDER BY created_at DESC LIMIT 5'
    );
    $stmt->execute([$room['id']]);
    $recentItems = $stmt->fetchAll();

    $title = 'Objets trouvés — Chambre ' . $room['number'] . ' — ' . $room['hotel_name'];
    ob_start();
    require __DIR__ . '/../views/room/found.php';
    echo ob_get_clean();
  }
}
