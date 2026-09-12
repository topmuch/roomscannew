<?php
// =============================================
// RoomScan — Housekeeping Controller (Gouvernante)
// =============================================

class HousekeepingController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /housekeeping — Kanban-style board grouped by status
  public function index() {
    $hotelId = current_hotel_id();

    // Stats
    $stats = [];
    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM housekeeping WHERE hotel_id = ? AND status = 'pending'"
    );
    $stmt->execute([$hotelId]);
    $stats['pending'] = (int) $stmt->fetchColumn();

    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM housekeeping WHERE hotel_id = ? AND status = 'in_progress'"
    );
    $stmt->execute([$hotelId]);
    $stats['in_progress'] = (int) $stmt->fetchColumn();

    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM housekeeping
       WHERE hotel_id = ? AND status = 'completed' AND DATE(completed_at) = CURDATE()"
    );
    $stmt->execute([$hotelId]);
    $stats['completed_today'] = (int) $stmt->fetchColumn();

    // All tasks, ordered by status then priority then date
    $stmt = $this->db->prepare(
      "SELECT h.*, r.number as room_number, r.floor as room_floor, u.name as assigned_name
       FROM housekeeping h
       JOIN rooms r ON h.room_id = r.id
       LEFT JOIN users u ON h.assigned_to = u.id
       WHERE h.hotel_id = ?
       ORDER BY
         FIELD(h.status, 'pending', 'in_progress', 'completed', 'inspected', 'flagged'),
         FIELD(h.priority, 'urgent', 'high', 'normal', 'low'),
         h.requested_at DESC"
    );
    $stmt->execute([$hotelId]);
    $tasks = $stmt->fetchAll();

    // Group by status
    $groups = [
      'pending'     => [],
      'in_progress' => [],
      'completed'   => [],
      'inspected'   => [],
      'flagged'     => [],
    ];
    foreach ($tasks as $t) {
      $groups[$t['status']][] = $t;
    }

    $title = 'Gouvernante — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/housekeeping/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /housekeeping/assign/{id}
  public function assign(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    $task = $this->findTask($id, $hotelId);
    if (!$task) {
      $this->notFound();
      return;
    }

    // Housekeeping staff
    $stmt = $this->db->prepare(
      "SELECT id, name FROM users
       WHERE hotel_id = ? AND role = 'housekeeping' AND is_active = 1
       ORDER BY name"
    );
    $stmt->execute([$hotelId]);
    $staff = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $assignedTo = (int) input('assigned_to', 0);
        $priority   = input('priority', 'normal');
        if (!in_array($priority, ['low', 'normal', 'high', 'urgent'], true)) {
          $priority = 'normal';
        }
        $notes = trim(input('notes', ''));

        if (!$assignedTo) {
          $error = 'Veuillez sélectionner une personne.';
        } else {
          $stmt = $this->db->prepare(
            "UPDATE housekeeping
             SET assigned_to = ?, priority = ?, notes = ?
             WHERE id = ? AND hotel_id = ?"
          );
          $stmt->execute([$assignedTo, $priority, $notes, $id, $hotelId]);
          redirect('/housekeeping');
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Affecter une tâche — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/housekeeping/assign.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /housekeeping/update/{id}
  public function update(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    $task = $this->findTask($id, $hotelId);
    if (!$task) {
      $this->notFound();
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $status = input('status', 'pending');
        $validStatuses = ['pending', 'in_progress', 'completed', 'inspected', 'flagged'];
        if (!in_array($status, $validStatuses, true)) {
          $status = 'pending';
        }

        // Checklist (JSON): bed_made, towels_changed, floor_cleaned, etc.
        $checklistKeys = [
          'bed_made', 'towels_changed', 'floor_cleaned', 'bathroom_cleaned',
          'trash_emptied', 'dusting_done', 'windows_cleaned', 'amenities_replaced',
        ];
        $checklist = [];
        foreach ($checklistKeys as $k) {
          $checklist[$k] = input($k) === '1';
        }

        $flaggedIssue = trim(input('flagged_issue', ''));
        $notes        = trim(input('notes', ''));

        // Timestamps
        $startedAt   = $task['started_at'];
        $completedAt = $task['completed_at'];
        if ($status === 'in_progress' && !$startedAt) {
          $startedAt = date('Y-m-d H:i:s');
        }
        if ($status === 'completed' && !$completedAt) {
          $completedAt = date('Y-m-d H:i:s');
        }
        // If moved back to pending/in_progress, keep timestamps intact.

        $stmt = $this->db->prepare(
          "UPDATE housekeeping
           SET status = ?, checklist = ?, flagged_issue = ?, notes = ?,
               started_at = ?, completed_at = ?
           WHERE id = ? AND hotel_id = ?"
        );
        $stmt->execute([
          $status,
          json_encode($checklist),
          $flaggedIssue,
          $notes,
          $startedAt,
          $completedAt,
          $id,
          $hotelId,
        ]);

        // Auto-update room status when task transitions.
        // completed → room = cleaning ; inspected → room = available.
        if ($status === 'completed') {
          $stmt = $this->db->prepare(
            "UPDATE rooms SET status = 'cleaning' WHERE id = ? AND hotel_id = ?"
          );
          $stmt->execute([$task['room_id'], $hotelId]);
        } elseif ($status === 'inspected') {
          $stmt = $this->db->prepare(
            "UPDATE rooms SET status = 'available' WHERE id = ? AND hotel_id = ?"
          );
          $stmt->execute([$task['room_id'], $hotelId]);
        }

        redirect('/housekeeping');
      }
    }

    $checklist = json_decode($task['checklist'] ?? '{}', true) ?: [];
    $csrf = csrf_token();
    $title = 'Mettre à jour la tâche — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/housekeeping/update.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /housekeeping/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    $error = '';

    $task = $this->findTask($id, $hotelId);
    if (!$task) {
      $this->notFound();
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $stmt = $this->db->prepare(
          "DELETE FROM housekeeping WHERE id = ? AND hotel_id = ?"
        );
        $stmt->execute([$id, $hotelId]);
        redirect('/housekeeping');
      }
    }

    $csrf = csrf_token();
    $title = 'Supprimer la tâche — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/housekeeping/delete.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // ---------- Helpers ----------

  private function findTask(int $id, int $hotelId): ?array {
    $stmt = $this->db->prepare(
      "SELECT h.*, r.number as room_number, r.id as room_id, r.floor as room_floor,
              u.name as assigned_name
       FROM housekeeping h
       JOIN rooms r ON h.room_id = r.id
       LEFT JOIN users u ON h.assigned_to = u.id
       WHERE h.id = ? AND h.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  private function notFound(): void {
    http_response_code(404);
    $title = '404 — Tâche introuvable';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    echo '<div class="text-center py-20">'
       . '<h1 class="text-6xl font-bold text-slate-300">404</h1>'
       . '<p class="mt-4 text-slate-500">Tâche introuvable.</p>'
       . '<a href="/housekeeping" class="mt-6 inline-block px-6 py-2 bg-navy-900 text-white rounded-lg">Retour à la gouvernante</a>'
       . '</div>';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }
}
