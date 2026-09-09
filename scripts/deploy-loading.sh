#!/bin/bash
#
# Patrimonasa — Actualización en el alojamiento Loading.
#
# Se ejecuta DESPUÉS de traer el código con:
#   cd /patrimonio.casetashormigon.es/app && git pull --ff-only origin main && bash scripts/deploy-loading.sh
# El pull exterior trae la versión actualizada de este script; por eso aquí
# no hay ningún pull ni reset: jamás se sobrescriben cambios locales.
#
# Qué hace, en orden seguro:
#   1. Comprueba PHP 8.3, extensiones, composer.lock y una compilación
#      válida en public/build (manifiesto + archivos existentes).
#   2. Comprueba que .env existe y está completo. Nunca lo crea, nunca
#      lo modifica y nunca toca APP_KEY.
#   3. Guarda copia recuperable de .env y de la BASE DE DATOS
#      (volcado MySQL o copia SQLite) antes de migrar.
#   4. Pone la app en mantenimiento, instala dependencias de producción
#      desde composer.lock, migra (solo añade) y regenera cachés.
#   5. Vuelve a poner la app en servicio. No usa Node ni npm, no ejecuta
#      seeders y no reinicia la base de datos.
#
# Si algo falla: el script se detiene y vuelve a levantar la app.
# Recuperación manual:
#   - Ver el error en pantalla y en storage/logs/laravel.log
#   - `cd /patrimonio.casetashormigon.es/app && git status` para ver el estado
#   - La copia está en ../copias-seguridad/ (base de datos + .env con fecha)
#   - `/ldnwebserver/php83/bin/php artisan up` para reabrir el servicio
#
set -euo pipefail

PHP_BIN="/ldnwebserver/php83/bin/php"
APP_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
COMPOSER_PHAR="$APP_ROOT/composer.phar"
BACKUP_DIR="$APP_ROOT/../copias-seguridad"
STAMP="$(date +%Y%m%d-%H%M%S)"

cd "$APP_ROOT"
echo "== Patrimonasa: actualizando en $APP_ROOT =="

# --- 0. La app nunca debe quedar cerrada por un error ---
cleanup() {
    "$PHP_BIN" artisan up >/dev/null 2>&1 || true
}
trap 'echo "!! Fallo en la actualización. Reabriendo el servicio..."; cleanup' ERR

env_value() {
    grep -E "^$1=" "$APP_ROOT/.env" | tail -n 1 | cut -d= -f2- | tr -d ' "'
}

# --- 1. Comprobaciones previas (no cambian nada) ---
test -x "$PHP_BIN" || { echo "!! No se encuentra $PHP_BIN"; exit 1; }
"$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || { echo "!! Se necesita PHP 8.3 o superior"; exit 1; }
for ext in mbstring openssl pdo pdo_mysql fileinfo tokenizer xml ctype json session; do
    "$PHP_BIN" -r "exit(extension_loaded('$ext') ? 0 : 1);" \
        || { echo "!! Falta la extensión PHP: $ext"; exit 1; }
done
test -f "$COMPOSER_PHAR" || { echo "!! No se encuentra composer.phar en $APP_ROOT"; exit 1; }
test -f "$APP_ROOT/composer.lock" || { echo "!! Falta composer.lock: el código traído de Git está incompleto"; exit 1; }
test -f "$APP_ROOT/public/build/manifest.json" \
    || { echo "!! Falta public/build: publica una versión con la interfaz compilada"; exit 1; }
"$PHP_BIN" -r '
$manifest = json_decode(file_get_contents($argv[1]), true);
if (!is_array($manifest) || $manifest === []) { exit(1); }
foreach ($manifest as $entry) {
    if (isset($entry["file"]) && !is_file(dirname($argv[1])."/".$entry["file"])) { exit(2); }
}' "$APP_ROOT/public/build/manifest.json" \
    || { echo "!! public/build está incompleto (manifiesto vacío o faltan archivos)"; exit 1; }

# --- 2. .env: debe existir y estar completo. Jamás se toca. ---
if [ ! -f "$APP_ROOT/.env" ]; then
    echo "!! Falta $APP_ROOT/.env — primera instalación sin terminar."
    echo "   Cópialo desde .env.production.example, rellena DB_* y genera la clave con:"
    echo "   $PHP_BIN artisan key:generate --force"
    exit 1
fi
for var in APP_KEY APP_URL DB_CONNECTION DB_DATABASE DB_USERNAME DB_PASSWORD; do
    if [ -z "$(env_value "$var")" ]; then
        echo "!! En .env falta o está vacía: $var. Complétala y vuelve a ejecutar."
        exit 1
    fi
done
echo "-- .env presente y completo (no se modifica)."

# --- 3. Copia recuperable de .env y de la base de datos ---
mkdir -p "$BACKUP_DIR"
cp "$APP_ROOT/.env" "$BACKUP_DIR/.env.$STAMP"
DB_CONNECTION="$(env_value DB_CONNECTION)"
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_FILE="$APP_ROOT/database/database.sqlite"
    if [ -f "$DB_FILE" ]; then
        cp "$DB_FILE" "$BACKUP_DIR/database.$STAMP.sqlite"
    else
        echo "-- Aviso: no hay database.sqlite que copiar (base vacía)."
    fi
else
    command -v mysqldump >/dev/null 2>&1 \
        || { echo "!! No hay mysqldump disponible: no puedo copiar la base de datos. Instálalo o avisa antes de migrar."; exit 1; }
    mysqldump --host="$(env_value DB_HOST)" --port="$(env_value DB_PORT)" \
        --user="$(env_value DB_USERNAME)" --password="$(env_value DB_PASSWORD)" \
        "$(env_value DB_DATABASE)" > "$BACKUP_DIR/database.$STAMP.sql" \
        || { echo "!! No se pudo copiar la base de datos. Revisa las credenciales de .env."; exit 1; }
fi
echo "-- Copia guardada en $BACKUP_DIR (database.$STAMP.* + .env.$STAMP)"

# --- 4. Mantenimiento, dependencias, migraciones y cachés ---
"$PHP_BIN" artisan down
"$PHP_BIN" "$COMPOSER_PHAR" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
"$PHP_BIN" artisan vendor:publish --tag=laravel-assets --ansi --force
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

# --- 5. Permisos sanos para escritura (sin 777) ---
chmod 775 "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" 2>/dev/null \
    || echo "-- Aviso: no se pudieron ajustar permisos, revisa storage/ si falla la escritura."

trap - ERR
"$PHP_BIN" artisan up
echo "== Patrimonasa actualizada correctamente =="
