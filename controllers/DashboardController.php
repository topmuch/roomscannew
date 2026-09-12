<?php
// =============================================
// RoomScan — Dashboard Controller
// =============================================

class DashboardController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /dashboard
  public function index() {
    $hotelId = current_hotel_id();

    // Stats
    $stats = [];

    // Today's check-ins
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE hotel_id = ? AND check_in = CURDATE() AND status IN ('confirmed','checked_in')");
    $stmt->execute([$hotelId]);
    $stats['checkins_today'] = (int) $stmt->fetchColumn();

    // Today's check-outs
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE hotel_id = ? AND check_out = CURDATE() AND status = 'checked_in'");
    $stmt->execute([$hotelId]);
    $stats['checkouts_today'] = (int) $stmt->fetchColumn();

    // Occupied rooms
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND status = 'occupied'");
    $stmt->execute([$hotelId]);
    $stats['occupied'] = (int) $stmt->fetchColumn();

    // Total rooms
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ?");
    $stmt->execute([$hotelId]);
    $stats['total_rooms'] = (int) $stmt->fetchColumn();

    // Occupancy rate
    $stats['occupancy_rate'] = $stats['total_rooms'] > 0
      ? round(($stats['occupied'] / $stats['total_rooms']) * 100)
      : 0;

    // Revenue this month
    $stmt = $this->db->prepare("SELECT COALESCE(SUM(total), 0) FROM reservations WHERE hotel_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled'");
    $stmt->execute([$hotelId]);
    $stats['revenue_month'] = (float) $stmt->fetchColumn();

    // Pending room service orders
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM room_service_orders WHERE hotel_id = ? AND status IN ('pending','confirmed','preparing')");
    $stmt->execute([$hotelId]);
    $stats['pending_orders'] = (int) $stmt->fetchColumn();

    // Housekeeping pending
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM housekeeping WHERE hotel_id = ? AND status IN ('pending','in_progress')");
    $stmt->execute([$hotelId]);
    $stats['housekeeping_pending'] = (int) $stmt->fetchColumn();

    // Pending service requests
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM service_requests WHERE hotel_id = ? AND status = 'pending'");
    $stmt->execute([$hotelId]);
    $stats['service_requests'] = (int) $stmt->fetchColumn();

    // Recent reservations
    $stmt = $this->db->prepare(
      "SELECT r.*, g.name as guest_name, rm.number as room_number
       FROM reservations r
       JOIN guests g ON r.guest_id = g.id
       JOIN rooms rm ON r.room_id = rm.id
       WHERE r.hotel_id = ?
       ORDER BY r.created_at DESC LIMIT 5"
    );
    $stmt->execute([$hotelId]);
    $recentReservations = $stmt->fetchAll();

    // Room status overview
    $stmt = $this->db->prepare(
      "SELECT status, COUNT(*) as count FROM rooms WHERE hotel_id = ? GROUP BY status"
    );
    $stmt->execute([$hotelId]);
    $roomStatuses = $stmt->fetchAll();

    $title = 'Tableau de bord — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/dashboard.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  public function notFound() {
    http_response_code(404);
    $title = '404 — Page non trouvée';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    echo '<div class="text-center py-20"><h1 class="text-6xl font-bold text-slate-300">404</h1><p class="mt-4 text-slate-500">Page non trouvée</p><a href="/dashboard" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg">Retour au dashboard</a></div>';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }
}
