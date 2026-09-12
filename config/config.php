<?php
// =============================================
// RoomScan — Configuration
// =============================================

// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'roomscan');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---- App ----
define('APP_NAME', 'RoomScan');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
define('APP_VERSION', '1.0.0');
define('DEBUG', getenv('DEBUG') === 'true' || true);

// ---- Session ----
define('SESSION_NAME', 'roomscan_session');
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days

// ---- Security ----
define('CSRF_TOKEN_NAME', '_csrf');
define('PASSWORD_MIN_LENGTH', 6);

// ---- Plans ----
define('PLANS', [
  'starter'  => ['price' => 15, 'max_rooms' => 10, 'max_users' => 2, 'qr_code' => false],
  'pro'      => ['price' => 39, 'max_rooms' => 50, 'max_users' => 5, 'qr_code' => true],
  'business' => ['price' => 89, 'max_rooms' => 999, 'max_users' => 20, 'qr_code' => true],
]);

// ---- Error reporting (dev) ----
if (DEBUG) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
} else {
  error_reporting(0);
  ini_set('display_errors', 0);
}

// =============================================
// Database connection (PDO)
// =============================================
try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );
} catch (PDOException $e) {
  if (DEBUG) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
  }
  die('Database connection failed. Please check your configuration.');
}

// =============================================
// Session start
// =============================================
session_name(SESSION_NAME);
session_set_cookie_params([
  'lifetime' => SESSION_LIFETIME,
  'path' => '/',
  'httponly' => true,
  'secure' => !DEBUG,
  'samesite' => 'Lax',
]);
session_start();

// =============================================
// Helper functions
// =============================================

// Generate CSRF token
function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf(): bool {
  $token = $_POST[CSRF_TOKEN_NAME] ?? '';
  return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Redirect
function redirect(string $path): void {
  header("Location: " . $path);
  exit;
}

// Get current user
function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

// Get current hotel_id
function current_hotel_id(): ?int {
  return $_SESSION['user']['hotel_id'] ?? null;
}

// Check if logged in
function is_logged_in(): bool {
  return isset($_SESSION['user']);
}

// Require auth
function require_auth(): void {
  if (!is_logged_in()) {
    redirect('/login');
  }
}

// Require role
function require_role(string ...$roles): void {
  require_auth();
  $userRole = $_SESSION['user']['role'] ?? '';
  if (!in_array($userRole, $roles)) {
    http_response_code(403);
    die('Accès refusé.');
  }
}

// Escape HTML
function e(?string $value): string {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Format price
function format_price(float $price): string {
  return number_format($price, 0, ',', ' ') . ' FCFA';
}

// JSON response
function json_response($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

// Get input
function input(string $key, $default = null) {
  return $_POST[$key] ?? $_GET[$key] ?? $default;
}

// Generate QR token
function generate_qr_token(): string {
  return bin2hex(random_bytes(32));
}

// Format date FR
function format_date(?string $date, string $format = 'd/m/Y'): string {
  if (!$date) return '—';
  $d = new DateTime($date);
  return $d->format($format);
}

// Days between two dates
function days_between(string $start, string $end): int {
  $s = new DateTime($start);
  $e = new DateTime($end);
  return max(1, $s->diff($e)->days);
}

// Room status labels
function room_status_label(string $status): string {
  $labels = [
    'available' => 'Disponible',
    'occupied' => 'Occupée',
    'cleaning' => 'En nettoyage',
    'maintenance' => 'Maintenance',
    'out_of_order' => 'Hors service',
  ];
  return $labels[$status] ?? $status;
}

function room_status_color(string $status): string {
  $colors = [
    'available' => 'green',
    'occupied' => 'red',
    'cleaning' => 'amber',
    'maintenance' => 'orange',
    'out_of_order' => 'gray',
  ];
  return $colors[$status] ?? 'gray';
}
