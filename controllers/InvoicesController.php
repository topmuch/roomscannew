<?php
// =============================================
// RoomScan — Invoices Controller (Factures)
// =============================================

class InvoicesController {
  private PDO $db;

  public function __construct(PDO $db) {
    $this->db = $db;
    require_auth();
  }

  // GET /invoices
  public function index(?string $param = null) {
    $hotelId = current_hotel_id();
    $search = trim($_GET['search'] ?? '');

    $sql =
      "SELECT i.*, g.name as guest_name, g.phone as guest_phone
       FROM invoices i
       LEFT JOIN guests g ON i.guest_id = g.id
       WHERE i.hotel_id = ?";
    $params = [$hotelId];

    if ($search !== '') {
      $sql .= " AND (i.invoice_number LIKE ? OR g.name LIKE ?)";
      $params[] = "%{$search}%";
      $params[] = "%{$search}%";
    }
    $sql .= " ORDER BY i.created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();

    $title = 'Factures — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/invoices/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET/POST /invoices/create
  public function create(?string $param = null) {
    $hotelId = current_hotel_id();
    $error = '';
    $success = '';

    // Fetch reservations (with guest + room info) for select
    $stmt = $this->db->prepare(
      "SELECT r.id, r.check_in, r.check_out, r.total,
              g.name as guest_name, g.id as guest_id,
              rm.number as room_number, rm.price as room_price
       FROM reservations r
       JOIN guests g ON r.guest_id = g.id
       JOIN rooms rm ON r.room_id = rm.id
       WHERE r.hotel_id = ? AND r.status NOT IN ('cancelled','no_show')
       ORDER BY r.check_in DESC LIMIT 200"
    );
    $stmt->execute([$hotelId]);
    $reservations = $stmt->fetchAll();

    // Fetch guests for select
    $stmt = $this->db->prepare(
      "SELECT id, name, phone, email FROM guests WHERE hotel_id = ? ORDER BY name LIMIT 500"
    );
    $stmt->execute([$hotelId]);
    $guests = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verify_csrf()) {
        $error = 'Token de sécurité invalide.';
      } else {
        $reservationId = input('reservation_id') ?: null;
        $guestId = (int) input('guest_id', 0);
        $taxRate = (float) input('tax_rate', 0);
        $paymentMethod = input('payment_method', 'cash');
        $initialPayment = (float) input('initial_payment', 0);

        // Items (JSON-encoded array from hidden input)
        $items = json_decode(input('items_json', '[]'), true);
        if (!is_array($items)) $items = [];

        // Filter empty rows
        $items = array_values(array_filter($items, function ($it) {
          return !empty($it['label']) && (float)($it['qty'] ?? 0) > 0;
        }));

        if (empty($items)) {
          $error = 'Ajoutez au moins une ligne à la facture.';
        } elseif (!$guestId) {
          $error = 'Veuillez sélectionner un client.';
        } else {
          // Compute totals
          $subtotal = 0;
          foreach ($items as $it) {
            $subtotal += (float)($it['qty'] ?? 0) * (float)($it['price'] ?? 0);
          }
          $taxAmount = round($subtotal * $taxRate / 100, 2);
          $total = $subtotal + $taxAmount;

          // Status based on initial payment
          if ($initialPayment >= $total && $total > 0) {
            $status = 'paid';
          } elseif ($initialPayment > 0) {
            $status = 'partial';
          } else {
            $status = 'unpaid';
          }

          // Generate invoice_number: INV-{hotel_id}-{year}-{sequential}
          $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM invoices WHERE hotel_id = ? AND YEAR(created_at) = ?"
          );
          $year = (int) date('Y');
          $stmt->execute([$hotelId, $year]);
          $seq = (int) $stmt->fetchColumn() + 1;
          $invoiceNumber = sprintf("INV-%d-%d-%04d", $hotelId, $year, $seq);

          // Insert
          $stmt = $this->db->prepare(
            "INSERT INTO invoices
              (hotel_id, reservation_id, guest_id, invoice_number, items, subtotal, tax_rate, tax_amount, total, paid, status, payment_method, paid_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
          );
          $paidAt = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
          $stmt->execute([
            $hotelId,
            $reservationId,
            $guestId,
            $invoiceNumber,
            json_encode($items),
            $subtotal,
            $taxRate,
            $taxAmount,
            $total,
            $initialPayment,
            $status,
            $paymentMethod,
            $paidAt,
          ]);
          $invoiceId = (int) $this->db->lastInsertId();
          redirect('/invoices/view/' . $invoiceId);
        }
      }
    }

    $csrf = csrf_token();
    $title = 'Nouvelle facture — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/invoices/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // GET /invoices/view/{id}
  public function view(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!$id) {
      redirect('/invoices');
    }

    // Fetch invoice with hotel + guest info
    $stmt = $this->db->prepare(
      "SELECT i.*, h.name as hotel_name, h.email as hotel_email, h.phone as hotel_phone,
              h.address as hotel_address, h.city as hotel_city, h.country as hotel_country,
              h.currency, h.logo, h.wifi_ssid,
              g.name as guest_name, g.email as guest_email, g.phone as guest_phone,
              g.address as guest_address, g.country as guest_country
       FROM invoices i
       JOIN hotels h ON i.hotel_id = h.id
       LEFT JOIN guests g ON i.guest_id = g.id
       WHERE i.id = ? AND i.hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
      redirect('/invoices');
    }

    $items = json_decode($invoice['items'] ?? '[]', true);
    if (!is_array($items)) $items = [];

    $title = 'Facture ' . $invoice['invoice_number'] . ' — RoomScan';
    ob_start();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/invoices/view.php';
    require __DIR__ . '/../views/layouts/footer.php';
    echo ob_get_clean();
  }

  // POST /invoices/markPaid/{id}
  public function markPaid(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!verify_csrf()) {
      redirect('/invoices/view/' . $id);
    }

    $stmt = $this->db->prepare(
      "UPDATE invoices
       SET paid = total, status = 'paid', paid_at = NOW(), payment_method = COALESCE(payment_method, 'cash')
       WHERE id = ? AND hotel_id = ?"
    );
    $stmt->execute([$id, $hotelId]);
    redirect('/invoices/view/' . $id);
  }

  // POST /invoices/delete/{id}
  public function delete(?string $id) {
    $hotelId = current_hotel_id();
    $id = (int) $id;
    if (!verify_csrf()) {
      redirect('/invoices');
    }

    $stmt = $this->db->prepare("DELETE FROM invoices WHERE id = ? AND hotel_id = ?");
    $stmt->execute([$id, $hotelId]);
    redirect('/invoices');
  }
}
