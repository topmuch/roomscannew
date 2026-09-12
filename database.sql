-- =============================================
-- RoomScan — SaaS Hôtelier Multi-tenant
-- MySQL Schema
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =============================================
-- TENANT (Hôtel)
-- =============================================
CREATE TABLE IF NOT EXISTS hotels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  domain VARCHAR(255) UNIQUE,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  address TEXT,
  city VARCHAR(100),
  country VARCHAR(100) DEFAULT 'Sénégal',
  currency VARCHAR(10) DEFAULT 'XOF',
  plan VARCHAR(20) DEFAULT 'starter',
  max_rooms INT DEFAULT 10,
  max_users INT DEFAULT 2,
  status VARCHAR(20) DEFAULT 'active',
  logo VARCHAR(255),
  wifi_ssid VARCHAR(255),
  wifi_password VARCHAR(255),
  checkin_time VARCHAR(10) DEFAULT '14:00',
  checkout_time VARCHAR(10) DEFAULT '12:00',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- USERS
-- =============================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'agent',
  -- admin, manager, reception, housekeeping, kitchen
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ROOM TYPES
-- =============================================
CREATE TABLE IF NOT EXISTS room_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  base_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  capacity INT DEFAULT 2,
  amenities TEXT,
  -- JSON: ["wifi","tv","ac","minibar","safe","balcony"]
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ROOMS (Chambres)
-- =============================================
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  number VARCHAR(20) NOT NULL,
  type_id INT,
  floor INT DEFAULT 1,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) DEFAULT 'available',
  -- available, occupied, cleaning, maintenance, out_of_order
  qr_token VARCHAR(64) UNIQUE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (type_id) REFERENCES room_types(id) ON DELETE SET NULL,
  UNIQUE KEY unique_room (hotel_id, number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- GUESTS (Clients)
-- =============================================
CREATE TABLE IF NOT EXISTS guests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(50),
  country VARCHAR(100),
  id_type VARCHAR(50),
  -- cni, passport, driver_license
  id_number VARCHAR(100),
  vip TINYINT(1) DEFAULT 0,
  preferences TEXT,
  -- JSON: {"floor":"high","bed":"king","notes":"allergie aux noix"}
  total_stays INT DEFAULT 0,
  total_spent DECIMAL(10,2) DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- RESERVATIONS
-- =============================================
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  guest_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  adults INT DEFAULT 1,
  children INT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'confirmed',
  -- pending, confirmed, checked_in, checked_out, cancelled, no_show
  room_price DECIMAL(10,2) DEFAULT 0,
  extras_total DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) DEFAULT 0,
  paid DECIMAL(10,2) DEFAULT 0,
  payment_method VARCHAR(50),
  -- cash, card, mobile_money, bank_transfer
  payment_status VARCHAR(20) DEFAULT 'unpaid',
  -- unpaid, partial, paid
  source VARCHAR(50) DEFAULT 'direct',
  -- direct, booking.com, airbnb, walk_in
  notes TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- HOUSEKEEPING (Gouvernante)
-- =============================================
CREATE TABLE IF NOT EXISTS housekeeping (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  room_id INT NOT NULL,
  assigned_to INT,
  -- user_id de la femme de chambre
  status VARCHAR(20) DEFAULT 'pending',
  -- pending, in_progress, completed, inspected, flagged
  priority VARCHAR(20) DEFAULT 'normal',
  -- low, normal, high, urgent
  checklist TEXT,
  -- JSON: {"bed_made":true,"towels_changed":true,"floor_cleaned":false,...}
  notes TEXT,
  flagged_issue TEXT,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  started_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- MENU ITEMS (Room Service)
-- =============================================
CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(100),
  -- breakfast, lunch, dinner, drinks, snacks
  image VARCHAR(255),
  available TINYINT(1) DEFAULT 1,
  prep_time INT DEFAULT 15,
  -- minutes
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ROOM SERVICE ORDERS
-- =============================================
CREATE TABLE IF NOT EXISTS room_service_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  room_id INT NOT NULL,
  reservation_id INT,
  items TEXT NOT NULL,
  -- JSON: [{"item_id":1,"qty":2,"price":5000},...]
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) DEFAULT 'pending',
  -- pending, confirmed, preparing, ready, delivered, cancelled
  guest_name VARCHAR(255),
  guest_phone VARCHAR(50),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SERVICE REQUESTS (via QR code)
-- =============================================
CREATE TABLE IF NOT EXISTS service_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  room_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  -- housekeeping, towels, maintenance, reception, extra_pillow, wake_up
  status VARCHAR(20) DEFAULT 'pending',
  -- pending, in_progress, completed, cancelled
  priority VARCHAR(20) DEFAULT 'normal',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LOST & FOUND (Objets trouvés)
-- =============================================
CREATE TABLE IF NOT EXISTS lost_found (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  type VARCHAR(10) NOT NULL,
  -- lost (perdu par client) or found (trouvé par staff)
  item_name VARCHAR(255) NOT NULL,
  description TEXT,
  room_id INT,
  location VARCHAR(255),
  photo VARCHAR(255),
  status VARCHAR(20) DEFAULT 'open',
  -- open, claimed, returned, disposed
  reported_by VARCHAR(255),
  -- guest name or staff name
  contact VARCHAR(255),
  -- phone or email of the guest
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INVOICES (Factures)
-- =============================================
CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  reservation_id INT,
  guest_id INT,
  invoice_number VARCHAR(50) UNIQUE,
  items TEXT NOT NULL,
  -- JSON: [{"label":"Chambre 101","qty":2,"price":25000},...]
  subtotal DECIMAL(10,2) DEFAULT 0,
  tax_rate DECIMAL(5,2) DEFAULT 0,
  tax_amount DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) DEFAULT 0,
  paid DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(20) DEFAULT 'unpaid',
  -- unpaid, partial, paid, cancelled
  payment_method VARCHAR(50),
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
  FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SETTINGS (par hôtel)
-- =============================================
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT,
  UNIQUE KEY unique_setting (hotel_id, setting_key),
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INVITATIONS
-- =============================================
CREATE TABLE IF NOT EXISTS invitations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'agent',
  token VARCHAR(64) UNIQUE,
  status VARCHAR(20) DEFAULT 'pending',
  expires_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- AUDIT LOG (optionnel)
-- =============================================
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT,
  user_id INT,
  action VARCHAR(100),
  entity VARCHAR(50),
  entity_id INT,
  details TEXT,
  ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INDEX
-- =============================================
CREATE INDEX idx_rooms_hotel ON rooms(hotel_id);
CREATE INDEX idx_reservations_hotel ON reservations(hotel_id);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_dates ON reservations(check_in, check_out);
CREATE INDEX idx_guests_hotel ON guests(hotel_id);
CREATE INDEX idx_housekeeping_hotel ON housekeeping(hotel_id);
CREATE INDEX idx_orders_hotel ON room_service_orders(hotel_id);
CREATE INDEX idx_lost_found_hotel ON lost_found(hotel_id);
CREATE INDEX idx_invoices_hotel ON invoices(hotel_id);

-- =============================================
-- DEFAULT ADMIN (password: roomscan2025)
-- =============================================
INSERT INTO hotels (name, email, plan, max_rooms, max_users, status)
VALUES ('RoomScan Demo', 'demo@roomscan.app', 'pro', 50, 5, 'active');

INSERT INTO users (hotel_id, email, password, name, role)
VALUES (1, 'admin@roomscan.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin RoomScan', 'admin');
