<?php
// =============================================
// RoomScan — Landing Page Controller (public, no auth)
// =============================================

class LandingController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
  }

  public function index() {
    $title = 'RoomScan — SaaS de gestion hôtelière';
    ob_start();
    require __DIR__ . '/../views/pages/landing.php';
    echo ob_get_clean();
  }
}
