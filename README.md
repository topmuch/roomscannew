# RoomScan — SaaS de Gestion Hôtelière

## Installation sur Hostinger

### 1. Upload
- Uploadez tous les fichiers dans `public_html/` via FTP ou File Manager

### 2. Base de données
- Dans hPanel → MySQL Databases, créez une base de données
- Notez les identifiants (nom DB, utilisateur, mot de passe)
- Importez `database.sql` via phpMyAdmin

### 3. Configuration
Éditez `config/config.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'votre_db_name');
define('DB_USER', 'votre_db_user');
define('DB_PASS', 'votre_db_password');
```

### 4. Connexion
- Allez sur `votre-domaine.com/login`
- Email : `admin@roomscan.app`
- Mot de passe : `roomscan2025`
- **Changez le mot de passe immédiatement !**

## Structure du projet

```
/config/         → Configuration (DB, helpers)
/controllers/    → Logique (Auth, Dashboard, Room, etc.)
/models/         → Modèles de données
/views/          → Templates HTML
/api/            → Endpoints AJAX
/public/         → CSS, JS, uploads
database.sql     → Schéma MySQL
index.php        → Routeur frontal
.htaccess        → URL rewriting + sécurité
```

## Modules

| Module | Description |
|--------|-------------|
| Dashboard | Vue d'ensemble (occupation, revenus, check-ins) |
| Réservations | Booking, calendrier, check-in/out |
| Chambres | Gestion chambres, types, statuts, QR codes |
| Clients | CRM guests, historique, préférences |
| Gouvernante | Housekeeping, statuts, affectations |
| Room Service | Menu, commandes via QR code |
| Objets trouvés | Lost & Found avec QR code |
| Factures | Factures, paiements |

## QR Code Chambre

Chaque chambre a un `qr_token` unique. Le QR code pointe vers :
```
https://votre-domaine.com/room/{qr_token}
```

Le client scanne et accède à :
- Room Service (menu)
- Services (ménage, serviettes, maintenance, réception)
- Objets perdus/trouvés
- Infos hôtel (WiFi, check-in/out)
