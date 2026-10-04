#!/usr/bin/env bash
set -Eeuo pipefail

# Instalador do Painel SSH Web V3 para Ubuntu 24.04.
# Uso: sudo bash ops/install.sh
# Não remove instalações existentes; pede confirmação antes de alterar o servidor.

APP_ROOT="${APP_ROOT:-/var/www/painelssh}"
WEB_ROOT="$APP_ROOT/gestorssh"
ENV_DIR="/etc/painelssh"
ENV_FILE="$ENV_DIR/painelssh.env"
APACHE_SITE="/etc/apache2/sites-available/painelssh.conf"
DB_NAME="${DB_NAME:-sshplus}"
DB_USER="${DB_USER:-painelssh}"
PHP_VERSION="${PHP_VERSION:-8.3}"

log(){ printf '[painelssh] %s\n' "$*"; }
fail(){ printf '[painelssh] ERRO: %s\n' "$*" >&2; exit 1; }
trap 'fail "Falha na linha $LINENO. Nenhuma limpeza destrutiva foi executada; revise a saída acima."' ERR

[[ "$(id -u)" -eq 0 ]] || fail "execute com sudo: sudo bash ops/install.sh"
command -v apt-get >/dev/null || fail "este instalador requer Ubuntu/Debian com apt-get"

if [[ -e "$APP_ROOT" ]]; then
  log "Diretório existente detectado: $APP_ROOT"
  read -r -p "Continuar sem sobrescrever arquivos existentes? [s/N] " answer
  [[ "$answer" =~ ^[sS]$ ]] || exit 0
fi

read -r -p "Domínio/hostname do painel (ex.: painel.exemplo.com): " APP_DOMAIN
[[ "$APP_DOMAIN" =~ ^[A-Za-z0-9.-]+$ ]] || fail "hostname inválido"
read -r -p "Login do primeiro administrador: " ADMIN_LOGIN
[[ "$ADMIN_LOGIN" =~ ^[A-Za-z0-9._-]{3,60}$ ]] || fail "login inválido"
read -r -s -p "Senha do administrador (mínimo 12 caracteres): " ADMIN_PASSWORD; printf '\n'
[[ "${#ADMIN_PASSWORD}" -ge 12 ]] || fail "a senha precisa ter no mínimo 12 caracteres"
read -r -p "E-mail do administrador: " ADMIN_EMAIL
[[ "$ADMIN_EMAIL" =~ ^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$ ]] || fail "e-mail inválido"
read -r -p "Nome do administrador [Administrador]: " ADMIN_NAME
ADMIN_NAME="${ADMIN_NAME:-Administrador}"
read -r -p "Instalar/ajustar Apache, PHP e MariaDB agora? [s/N] " answer
[[ "$answer" =~ ^[sS]$ ]] || fail "instalação cancelada antes de alterar o sistema"

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y apache2 mariadb-server "php${PHP_VERSION}" "libapache2-mod-php${PHP_VERSION}" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-mysql" "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" "php${PHP_VERSION}-curl" "php${PHP_VERSION}-zip" unzip git openssl
systemctl enable --now apache2 mariadb

DB_PASSWORD="$(openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 40)"
mkdir -p "$ENV_DIR"
chmod 750 "$ENV_DIR"

mysql --protocol=socket -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

if [[ -d "$APP_ROOT/.git" ]]; then
  log "Usando checkout existente em $APP_ROOT"
elif [[ -d "$(pwd)/.git" && -d "$(pwd)/gestorssh" ]]; then
  mkdir -p "$(dirname "$APP_ROOT")"
  cp -a "$(pwd)" "$APP_ROOT"
else
  fail "execute o script a partir da raiz clonada do repositório painelssh"
fi

[[ -f "$APP_ROOT/database/bdgestorssh.secure.sql" ]] || fail "dump SQL seguro não encontrado"
[[ -f "$APP_ROOT/composer.json" ]] || fail "composer.json não encontrado"
mysql --protocol=socket -uroot "$DB_NAME" < "$APP_ROOT/database/bdgestorssh.secure.sql"

cat > "$ENV_FILE" <<EOF
APP_NAME='SSH WEB'
APP_TIMEZONE=America/Sao_Paulo
APP_DEBUG=0
FORCE_SECURE_COOKIES=0
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
APP_URL=http://$APP_DOMAIN
EOF
chown root:www-data "$ENV_FILE"
chmod 640 "$ENV_FILE"

cat > "$APACHE_SITE" <<EOF
<VirtualHost *:80>
    ServerName $APP_DOMAIN
    DocumentRoot $WEB_ROOT
    SetEnv APP_TIMEZONE America/Sao_Paulo
    SetEnv APP_DEBUG 0
    SetEnv FORCE_SECURE_COOKIES 0
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_PORT 3306
    SetEnv DB_NAME $DB_NAME
    SetEnv DB_USER $DB_USER
    SetEnv DB_PASSWORD $DB_PASSWORD
    SetEnv APP_URL http://$APP_DOMAIN
    <Directory $WEB_ROOT>
        AllowOverride All
        Options -Indexes +FollowSymLinks
        Require all granted
        <FilesMatch "\\.(?:sql|md|env|json|lock|sh)$">
            Require all denied
        </FilesMatch>
    </Directory>
    <Directory $APP_ROOT>
        Require all denied
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/painelssh_error.log
    CustomLog \${APACHE_LOG_DIR}/painelssh_access.log combined
</VirtualHost>
EOF

chown -R root:www-data "$APP_ROOT"
find "$APP_ROOT" -type d -exec chmod 750 {} +
find "$APP_ROOT" -type f -exec chmod 640 {} +
chmod 750 "$WEB_ROOT"
a2enmod rewrite headers env >/dev/null
a2dissite 000-default.conf >/dev/null 2>&1 || true
a2ensite painelssh.conf >/dev/null
apache2ctl configtest
systemctl reload apache2

if [[ -f "$APP_ROOT/vendor/autoload.php" ]]; then
  log "Dependências vendorizadas já presentes; Composer não foi executado automaticamente."
elif command -v composer >/dev/null 2>&1; then
  (cd "$APP_ROOT" && composer install --no-dev --prefer-dist --optimize-autoloader)
else
  log "AVISO: Composer não está instalado e vendor/autoload.php não existe."
fi

set -a
source "$ENV_FILE"
set +a
php "$APP_ROOT/bin/create_admin.php" "$ADMIN_LOGIN" "$ADMIN_PASSWORD" "$ADMIN_NAME" "$ADMIN_EMAIL"
unset ADMIN_PASSWORD DB_PASSWORD

log "Instalação concluída em $APP_ROOT"
log "URL HTTP inicial: http://$APP_DOMAIN"
log "Configure HTTPS antes de uso público e depois altere FORCE_SECURE_COOKIES para 1."
log "O módulo appss continua legado/ofuscado e deve ser validado em staging antes de produção."
