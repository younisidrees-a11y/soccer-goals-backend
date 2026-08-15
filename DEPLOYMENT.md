# Deploying The Soccer Goals to your VPS

This guide assumes a fresh Ubuntu VPS (22.04 or 24.04 LTS — the default on
almost every provider). If your VPS ships with a different OS, tell me and
I'll adjust the package-manager commands.

Everything here is written to be copy-pasted over SSH once the VPS is ready.
Nothing in this file touches your local machine or the live site — it's just
the reference for when you're ready to run these commands on the server.

---

## 0. What you'll need before starting

- [ ] SSH access to the VPS (IP address, root or sudo user, password or SSH key)
- [ ] Your domain name, with access to its DNS settings
- [ ] This codebase available somewhere the server can pull it from — easiest
      is a private GitHub/GitLab repo; the alternative is `scp`/`rsync`
      straight from this machine (covered in step 3b)

**Note:** this app does **not** need Node/npm on the server — the site's CSS/JS
are plain static files, not built through Vite. Skip that step entirely.

---

## 1. Point your domain at the VPS

In your domain registrar / DNS provider, add:

| Type | Name | Value |
|------|------|-------|
| A    | `@`  | your VPS IP address |
| A    | `www` | your VPS IP address |

DNS can take a few minutes to a few hours to propagate. You can start the
server setup below while you wait.

---

## 2. Initial server setup

SSH in, then:

```bash
apt update && apt upgrade -y
apt install -y software-properties-common curl unzip git

# Basic firewall
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
```

## 3. Install PHP 8.3, MySQL, Nginx, Composer

```bash
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y nginx mysql-server \
  php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-sqlite3

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Secure MySQL and create the production database:

```bash
mysql_secure_installation

mysql -u root -p
```
```sql
CREATE DATABASE thesoccergoals CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'soccergoals'@'localhost' IDENTIFIED BY 'CHOOSE-A-STRONG-PASSWORD-HERE';
GRANT ALL PRIVILEGES ON thesoccergoals.* TO 'soccergoals'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3b. Get the code onto the server

**Option A — git (recommended, makes future updates a one-line `git pull`):**

```bash
mkdir -p /var/www
cd /var/www
git clone <your-repo-url> soccer-goals-backend
cd soccer-goals-backend
```

**Option B — copy straight from this machine (no git needed for the first deploy):**

Run this from your **local** machine (not the server), once the VPS is reachable:

```bash
rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude '.env' \
  "E:/Claude Folder/soccer-goals-backend/" root@YOUR_VPS_IP:/var/www/soccer-goals-backend/
```

## 4. Install dependencies

```bash
cd /var/www/soccer-goals-backend
composer install --optimize-autoloader --no-dev
```

## 5. Configure `.env`

```bash
cp .env.production.example .env
nano .env    # fill in DB password, domain, and generate a new FILAMENT_PATH
php artisan key:generate
```

See `.env.production.example` in this project for the full template with
inline notes on every value that needs changing.

**Do not reuse the local dev admin password or the `FILAMENT_PATH` from
your local `.env`** — this file has been read aloud in a chat transcript
this session, so treat both as burned. Generate fresh ones (see the
template for how).

## 6. Set permissions, migrate, seed

```bash
chown -R www-data:www-data /var/www/soccer-goals-backend
chmod -R 775 storage bootstrap/cache

php artisan migrate --force
php artisan db:seed --force   # only if you want the current demo season data live —
                               # see "Real data vs demo data" below before running this

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Nginx site config

Copy `nginx.conf.example` from this project to
`/etc/nginx/sites-available/soccergoals`, replace `yourdomain.com` with your
real domain, then:

```bash
ln -s /etc/nginx/sites-available/soccergoals /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

## 8. SSL (Let's Encrypt)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot edits the Nginx config to add HTTPS and sets up auto-renewal.

## 9. Verify

- [ ] `https://yourdomain.com` loads the homepage
- [ ] `https://yourdomain.com/leagues/premier-league` works
- [ ] `https://yourdomain.com/<your-new-FILAMENT_PATH>/login` loads the admin login
- [ ] You can log in with the new admin credentials you created (not the local dev ones)
- [ ] `php artisan tinker` on the server can query the DB successfully

---

## Real data vs. demo data

Everything seeded into this app so far — scores, standings, news articles,
player stats — is **fictional demo content** generated during development,
not real football data. Before this goes properly live, decide:

- Keep the demo season as placeholder content while you populate real data
  through the admin panel, or
- Wipe it (`php artisan migrate:fresh` without `--seed`) and start clean, or
- Replace it with a real data feed (would need a licensed sports-data API —
  this was flagged as an open decision back in the original backend plan
  and still hasn't been resolved)

## Ongoing deploys after this first one

```bash
cd /var/www/soccer-goals-backend
git pull
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
