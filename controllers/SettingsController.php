<?php
// =============================================
// RoomScan — Settings Controller
// =============================================

class SettingsController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /settings — redirect to /settings/hotel
  public function index(?string $param = null) {
    redirect('/settings/hotel');
  }

  // GET/POST /settings/hotel
  public function hotel(?string $param = null) {
    $hotelId = current_hotel_id();
    $error = '';
    $success = '';

    // Fetch current hotel
    $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotelId]);
    $hotel = $stmt->fetch();

    if (!$hotel) {
      redirect('/dashboard');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $name = trim(input('name', ''));
        $email = trim(input('email', ''));
        $phone = trim(input('phone', ''));
        $address = trim(input('address', ''));
        $city = trim(input('city', ''));
        $country = trim(input('country', 'Sénégal'));
        $currency = trim(input('currency', 'XOF'));
        $wifiSsid = trim(input('wifi_ssid', ''));
        $wifiPassword = trim(input('wifi_password', ''));
        $checkinTime = trim(input('checkin_time', '14:00'));
        $checkoutTime = trim(input('checkout_time', '12:00'));

        if (!$name || !$email) {
          $error = 'Le nom et l\'email sont obligatoires.';
        } else {
          // Logo upload (optional)
          $logoPath = $hotel['logo'];
          if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleUpload('logo', 'hotel_logo_' . $hotelId);
            if ($uploaded === null) {
              $error = 'Logo invalide (format jpg/png/webp, max 2 Mo).';
            } else {
              $logoPath = $uploaded;
            }
          }

          if (!$error) {
            $stmt = $this->db->prepare(
              "UPDATE hotels
               SET name = ?, email = ?, phone = ?, address = ?, city = ?, country = ?,
                   currency = ?, logo = ?, wifi_ssid = ?, wifi_password = ?,
                   checkin_time = ?, checkout_time = ?
               WHERE id = ?"
            );
            $stmt->execute([
              $name, $email, $phone, $address, $city, $country,
              $currency, $logoPath, $wifiSsid, $wifiPassword,
              $checkinTime, $checkoutTime, $hotelId
            ]);

            // Refresh hotel
            $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->execute([$hotelId]);
            $hotel = $stmt->fetch();

            // Keep session hotel_name in sync
            $_SESSION['user']['hotel_name'] = $hotel['name'];

            $success = 'Paramètres de l\'hôtel enregistrés.';
          }
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Paramètres hôtel — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/settings/hotel.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /settings/users
  public function users(?string $param = null) {
    $hotelId = current_hotel_id();
    $error = '';
    $success = '';

    // Get current hotel (for max_users)
    $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotelId]);
    $hotel = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $name = trim(input('name', ''));
        $email = trim(input('email', ''));
        $role = input('role', 'agent');
        $password = input('password', '');

        $allowedRoles = ['admin', 'manager', 'reception', 'housekeeping', 'kitchen'];

        if (!$name || !$email || !$password) {
          $error = 'Tous les champs sont requis.';
        } elseif (!in_array($role, $allowedRoles)) {
          $error = 'Rôle invalide.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
          $error = 'Mot de passe trop court (min ' . PASSWORD_MIN_LENGTH . ' caractères).';
        } else {
          // Check email uniqueness
          $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
          $stmt->execute([$email]);
          if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
          } else {
            // Check plan limit
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE hotel_id = ?");
            $stmt->execute([$hotelId]);
            $userCount = (int) $stmt->fetchColumn();
            if ($userCount >= (int) $hotel['max_users']) {
              $error = 'Limite d\'utilisateurs atteinte pour votre plan (' . $hotel['max_users'] . ').';
            } else {
              $hash = password_hash($password, PASSWORD_DEFAULT);
              $stmt = $this->db->prepare(
                "INSERT INTO users (hotel_id, email, password, name, role, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)"
              );
              $stmt->execute([$hotelId, $email, $hash, $name, $role]);
              $success = 'Utilisateur créé avec succès.';
            }
          }
        }
      }
    }

    // List users
    $stmt = $this->db->prepare(
      "SELECT * FROM users WHERE hotel_id = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$hotelId]);
    $users = $stmt->fetchAll();

    $csrf = csrf_token();
    $title = 'Utilisateurs — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/settings/users.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /settings/userDelete/{id}
  public function userDelete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!verify_csrf()) {
      redirect('/settings/users');
    }

    // Prevent self-deletion
    $currentUserId = $_SESSION['user']['id'] ?? 0;
    if ($id === (int) $currentUserId) {
      redirect('/settings/users');
    }

    $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND hotel_id = ? AND role != 'admin'");
    $stmt->execute([$id, $hotelId]);
    redirect('/settings/users');
  }

  // GET /settings/plan
  public function plan(?string $param = null) {
    $hotelId = current_hotel_id();

    $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotelId]);
    $hotel = $stmt->fetch();

    // Count usage
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ?");
    $stmt->execute([$hotelId]);
    $roomsCount = (int) $stmt->fetchColumn();

    $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE hotel_id = ?");
    $stmt->execute([$hotelId]);
    $usersCount = (int) $stmt->fetchColumn();

    $currentPlan = $hotel['plan'] ?? 'starter';
    $plans = PLANS;

    $title = 'Plan & Abonnement — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/settings/plan.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // ---- Helpers ----

  /**
   * Handle a file upload. Validates type & size.
   * Returns the public URL on success, or null on failure.
   */
  private function handleUpload(string $field, string $basenamePrefix): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
      return null;
    }
    $file = $_FILES[$field];

    // Size check (<2 MB)
    if ($file['size'] > 2 * 1024 * 1024) {
      return null;
    }

    // Type check via mime
    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowedMimes[$mime])) {
      return null;
    }
    $ext = $allowedMimes[$mime];

    $filename = $basenamePrefix . '_' . time() . '.' . $ext;
    $dest = __DIR__ . '/../public/uploads/' . $filename;

    if (!is_dir(dirname($dest))) {
      @mkdir(dirname($dest), 0775, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      return null;
    }

    return '/public/uploads/' . $filename;
  }
}
