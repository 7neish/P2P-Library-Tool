# P2P Tool Library — Project Checklist

## Setup

1. Install XAMPP/Laragon (PHP 8+, MySQL).
2. Copy this folder to `htdocs/p2p-tool-library`.
3. Open phpMyAdmin → import `database/schema.sql`.
4. Confirm credentials in `includes/Database.php` (root / no password).
5. Visit `http://localhost/p2p-tool-library/`.

## Demo accounts (password: `password123`)

- admin@library.com — ADMIN
- librarian@library.com — LIBRARIAN
- tech@library.com — TECHNICIAN
- ahmed@example.com / mariam@example.com / khaled@example.com — MEMBER

## Implemented requirements

- [x] #1 KYC verification (member submit + librarian approval)
- [x] #2 Tool documentation (tool_documents table + seed data)
- [x] #3 Dynamic availability with cleaning buffer
- [x] #4 Geospatial discovery (Haversine)
- [x] #5 Multi-tier pricing engine (hourly/daily/weekly + tier discount)
- [x] #6 Trust-score algorithm (recalc on review)
- [x] #7 Smart reservation (conflict + buffer check)
- [x] #8 Escrow management (hold + release)
- [x] #10 Encrypted messaging (base64 placeholder)
- [x] #14 QR handover code
- [x] #17 Damage / dispute mediation hub
- [x] #19 Member suspension & blacklist
- [x] #23 Zone management (seeded)
- [x] #24 Hierarchical categories
- [x] #28 Librarian dashboard (stats)
- [x] #29 Maintenance logs
- [x] Bcrypt password hashing
- [x] mysqli + manual escape (no PDO)
- [x] Session-based auth + role gating
- [x] Bootstrap 5 UI via CDN
- [x] MVC: models / controllers (require views) / views (PHP+HTML)

## File structure

```
database/schema.sql
includes/{Database.php, Auth.php, helpers.php}
models/{User, Tool, Booking, Escrow, Category, Zone, Review, Message, Maintenance, DamageReport}.php
controllers/{auth/{login,logout,register}, tools/{create,list,my_tools,nearby,show}, bookings/{confirm,create,my_bookings,review,show}, escrow/{pending}, librarian/{dashboard,disputes,kyc,members}, members/{kyc,messages}, maintenance/{create,list}}/*.php
views/{auth/{login,register}, tools/{create,list,my_tools,nearby,show}, bookings/{create,my_bookings,show}, escrow/{pending}, librarian/{add_members,dashboard,disputes,kyc,members}, members/{kyc,messages}, maintenance/{create,list}, shared/{dashboard_shell_bottom,dashboard_shell_top}}/*.php
assets/css/style.css
index.php
```
