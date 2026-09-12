<?php
// =============================================
// RoomScan — Auth Controller
// =============================================

class AuthController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
  }

  // GET /login
  public function index() {
    $this->login();
  }

  // GET/POST /login
  public function login() {
    if (is_logged_in()) {
      redirect('/dashboard');
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $email = trim(input('email', ''));
        $password = input('password', '');

        if (!$email || !$password) {
          $error = 'Email et mot de passe requis.';
        } else {
          $stmt = $this->db->prepare(
            'SELECT u.*, h.name as hotel_name, h.plan, h.status as hotel_status
             FROM users u
             JOIN hotels h ON u.hotel_id = h.id
             WHERE u.email = ? AND u.is_active = 1'
          );
          $stmt->execute([$email]);
          $user = $stmt->fetch();

          if ($user && password_verify($password, $user['password'])) {
            if ($user['hotel_status'] !== 'active') {
              $error = 'Votre hôtel est suspendu. Contactez le support.';
            } else {
              $_SESSION['user'] = [
                'id' => $user['id'],
                'hotel_id' => $user['hotel_id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'hotel_name' => $user['hotel_name'],
                'plan' => $user['plan'],
              ];
              session_regenerate_id(true);
              redirect('/dashboard');
            }
          } else {
            $error = 'Identifiants incorrects.';
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Connexion — RoomScan';
    ob_start();
    require __DIR__ . '/../views/auth/login.php';
    echo ob_get_clean();
  }

  // GET/POST /register
  public function register() {
    if (is_logged_in()) {
      redirect('/dashboard');
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $hotelName = trim(input('hotel_name', ''));
        $name = trim(input('name', ''));
        $email = trim(input('email', ''));
        $password = input('password', '');

        if (!$hotelName || !$name || !$email || !$password) {
          $error = 'Tous les champs sont requis.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
          $error = 'Le mot de passe doit faire au moins ' . PASSWORD_MIN_LENGTH . ' caractères.';
        } else {
          // Check if email exists
          $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
          $stmt->execute([$email]);
          if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
          } else {
            // Create hotel
            $stmt = $this->db->prepare(
              'INSERT INTO hotels (name, email, plan, max_rooms, max_users, status)
               VALUES (?, ?, "starter", 10, 2, "active")'
            );
            $stmt->execute([$hotelName, $email]);
            $hotelId = (int) $this->db->lastInsertId();

            // Create user (admin)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
              'INSERT INTO users (hotel_id, email, password, name, role, is_active)
               VALUES (?, ?, ?, ?, "admin", 1)'
            );
            $stmt->execute([$hotelId, $email, $hash, $name]);
            $userId = (int) $this->db->lastInsertId();

            // Session
            $_SESSION['user'] = [
              'id' => $userId,
              'hotel_id' => $hotelId,
              'email' => $email,
              'name' => $name,
              'role' => 'admin',
              'hotel_name' => $hotelName,
              'plan' => 'starter',
            ];
            session_regenerate_id(true);
            redirect('/dashboard');
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Créer un compte — RoomScan';
    ob_start();
    require __DIR__ . '/../views/auth/register.php';
    echo ob_get_clean();
  }

  // GET /logout
  public function logout() {
    session_destroy();
    redirect('/login');
  }
}
