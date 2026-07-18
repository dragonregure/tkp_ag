# TKP AG

Laravel monolith untuk pencatatan penjualan, pembayaran bertahap, master user, dan master item. Frontend menggunakan Blade dengan theme AdminLTE.

## Manual Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Login awal:

- Email: `admin@example.com`
- Password: `password`

Seeder juga membuat data demo realistis untuk seluruh modul:

- 5 user demo dengan role admin, manager, sales, cashier, dan inventory.
- 24 item katalog.
- 72 penjualan demo dengan 180 baris item.
- 75 pembayaran, termasuk status belum dibayar, pembayaran bertahap, dan lunas.

## Docker Setup

```bash
docker compose up --build
```

Service utama:

- App: `http://localhost:8000`
- Swagger: `http://localhost:8000/api/documentation`
- phpMyAdmin: `http://localhost:8080`
- Mailpit: `http://localhost:8025`
- MySQL host port default: `3307`

Container `app`, `queue`, dan `scheduler` berjalan terpisah. Hanya `app` yang menjalankan migration dan seeder.

## Xdebug

Xdebug aktif di container Laravel dengan konfigurasi default:

- Client host: `host.docker.internal`
- Client port: `9003`
- IDE key: `TKP_AG`
- PHP IDE server name: `tkp-ag`

Pastikan IDE mendengarkan koneksi Xdebug di port `9003`. Untuk menonaktifkan sementara:

```bash
XDEBUG_MODE=off docker compose up -d --build
```

## Validation

```bash
composer test
composer analyse
composer lint
php artisan route:list
php artisan l5-swagger:generate
```
