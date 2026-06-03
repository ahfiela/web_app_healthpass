# PANDUAN DEPLOYMENT & HOSTING (BACKEND LARAVEL)

Dokumen ini berisi panduan untuk mengonlinekan backend Laravel Anda (baik secara lokal menggunakan tunnel maupun ke server produksi internet) agar dapat dihubungkan ke aplikasi mobile Flutter.

---

## 💻 1. OPSI A: Pengujian Lokal dengan Jaringan Wi-Fi (Gratis & Cepat)
Jika Anda ingin mengetes aplikasi dari HP fisik secara langsung tanpa keluar biaya hosting:

1. **Samakan Jaringan Wi-Fi**: Hubungkan komputer/laptop Anda dan perangkat HP ke satu Wi-Fi/Hotspot yang sama.
2. **Dapatkan IP Lokal Komputer**:
   * Buka CMD di Windows, jalankan `ipconfig`.
   * Catat **IPv4 Address** Wi-Fi Anda (contoh: `192.168.1.15`).
3. **Jalankan Laravel Bind Jaringan**:
   Jalankan Laravel menggunakan host `0.0.0.0` agar komputer memperbolehkan akses masuk dari perangkat lain di jaringan lokal:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. **Hubungkan Flutter**: Masukkan URL `http://192.168.1.15:8000` (sesuaikan IP komputer Anda) ke berkas `config.dart` di proyek Flutter.

---

## 🌐 2. OPSI B: Menggunakan Local Tunneling (Ngrok) - *Sangat Direkomendasikan untuk Demo*
Jika Anda ingin server lokal Anda bisa diakses dari mana saja secara online secara instan tanpa perlu sewa VPS:

1. Unduh dan instal **Ngrok** di komputer Anda (https://ngrok.com).
2. Jalankan server Laravel Anda secara normal: `php artisan serve` (berjalan di port `8000`).
3. Buka terminal baru (CMD/PowerShell) lalu jalankan perintah:
   ```bash
   ngrok http 8000
   ```
4. Ngrok akan memberikan URL publik HTTPS gratis, contohnya:
   `https://abcd-123-45.ngrok-free.app`
5. Salin URL HTTPS tersebut dan masukkan ke proyek Flutter pada berkas `config.dart`.

---

## 🚀 3. OPSI C: Cloud Platform (Railway / Render) - *Tingkat Menengah*
Opsi deployment gratis/murah yang otomatis melakukan build ulang setiap kali Anda melakukan push ke GitHub.

### Langkah Setup di Railway:
1. Hubungkan akun GitHub Anda ke **Railway** (https://railway.app).
2. Buat proyek baru dan pilih repositori `web_app_healthpass`.
3. Tambahkan **Database Service** (PostgreSQL atau MySQL) di Railway.
4. Konfigurasikan **Variables** (`.env`) pada Railway:
   * `APP_ENV=production`
   * `APP_DEBUG=false`
   * `APP_KEY=base64:...` (Salin dari `.env` lokal Anda)
   * `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Arahkan sesuai database Railway yang Anda buat).
5. Set **Start Command** pada settings deployment:
   ```bash
   php artisan migrate --force && php artisan db:seed --force && apache2-foreground
   ```
   *(Atau sesuaikan dengan buildpack PHP Nginx/Apache milik platform).*

---

## 🎛️ 4. OPSI D: Hosting di VPS Linux (DigitalOcean / Vultr / Linode) - *Skala Produksi*
Jika Anda membutuhkan performa maksimal, server mandiri, dan domain kustom:

### Langkah Inisialisasi Server:
1. Sewa VPS dengan OS **Ubuntu 22.04 LTS** atau versi lebih baru.
2. Instal stack web server (LEMP stack - Nginx, MySQL, PHP 8.2+):
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install nginx mysql-server php-fpm php-mysql php-cli php-mbstring php-xml php-bcmath php-curl php-zip -y
   ```
3. Pasang **Composer** dan clone repositori Git Anda ke `/var/www/healthpass-backend`.

### Langkah Konfigurasi Laravel:
1. Duplikat berkas `.env.example` menjadi `.env`, lalu konfigurasikan database dan kredensial.
2. Jalankan perintah instalasi dependency dan migrasi database:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```
3. Atur hak akses folder agar dapat ditulis oleh web server (Nginx/Apache):
   ```bash
   sudo chown -R www-data:www-data /var/www/healthpass-backend/storage
   sudo chown -R www-data:www-data /var/www/healthpass-backend/bootstrap/cache
   ```

### Konfigurasi Virtual Host Nginx:
Buat berkas konfigurasi Nginx baru (`/etc/nginx/sites-available/healthpass`):
```nginx
server {
    listen 80;
    server_name api.nama-domain-anda.com;
    root /var/www/healthpass-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Sesuaikan versi PHP Anda
    }

    location ~ /\.ht {
        deny all;
    }
}
```
Aktifkan konfigurasi dan muat ulang Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/healthpass /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### Memasang SSL (HTTPS) Gratis via Certbot:
Aplikasi Android & iOS memblokir request HTTP polos secara default. Anda wajib memasang HTTPS menggunakan Certbot:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d api.nama-domain-anda.com
```
Ikuti petunjuk di layar, lalu pilih opsi untuk mengalihkan (*redirect*) seluruh lalu lintas HTTP ke HTTPS secara otomatis.
