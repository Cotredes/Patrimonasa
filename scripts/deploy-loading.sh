#!/bin/bash
#
# Patrimonasa — Actualización en el alojamiento Loading.
#
# Qué hace, en orden seguro:
#   1. Comprueba PHP 8.3, extensiones, Composer, .env y que la interfaz
#      compilada (public/build) corresponde al commit desplegado.
#   2. Guarda copia de .env y de la base de datos SQLite (si se usa).
#   3. Pone la app en mantenimiento, actualiza desde main (solo avance
#      directo, sin sobrescribir cambios locales), instala dependencias
#      de producción, completa las tareas pendientes de Composer,
#      genera APP_KEY solo si falta, migra y regenera cachés.
#   4. Vuelve a poner la app en servicio.
#
# Uso (por SSH, desde cualquier carpeta):
#   bash /patrimonio.casetashormigon.es/app/scripts/deploy-loading.sh
#
# La interfaz compilada NO se genera en el servidor (no hay Node).
# Flujo de interfaz: en local, `npm run build`, anotar el commit con
#   git rev-parse HEAD > public/build/.deploy-commit
# y subir public/build al servidor antes de ejecutar este script.
#
# Si algo falla: el script se detiene y vuelve a levantar la app.
# Recuperación manual:
#   - Ver el error en pantalla y en storage/logs/laravel.log
#   - `git -C /patrimonio.casetashormigon.es/app status` para ver el estado
#   - Restaurar .env desde copias-seguridad/.env.<fecha> si hiciera falta
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

# --- 1. Comprobaciones previas (no cambian nada) ---
test -x "$PHP_BIN" || { echo "!! No se encuentra $PHP_BIN"; exit 1; }
"$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || { echo "!! Se necesita PHP 8.3 o superior"; exit 1; }
for ext in mbstring openssl pdo fileinfo tokenizer xml ctype json session; do
    "$PHP_BIN" -r "exit(extension_loaded('$ext') ? 0 : 1);" \
        || { echo "!! Falta la extensión PHP: $ext"; exit 1; }
done
test -f "$COMPOSER_PHAR" || { echo "!! No se encuentra composer.phar en $APP_ROOT"; exit 1; }
test -f "$APP_ROOT/.env" || { echo "!! Falta $APP_ROOT/.env (créalo antes, ver README)"; exit 1; }
test -f "$APP_ROOT/public/build/manifest.json" \
    || { echo "!! Falta public/build: sube la interfaz compilada antes de actualizar"; exit 1; }
if [ -f "$APP_ROOT/public/build/.deploy-commit" ]; then
    BUILD_COMMIT="$(cat "$APP_ROOT/public/build/.deploy-commit")"
    HEAD_COMMIT="$(git -C "$APP_ROOT" rev-parse HEAD)"
    if [ "$BUILD_COMMIT" != "$HEAD_COMMIT" ]; then
        echo "!! public/build corresponde a $BUILD_COMMIT pero el código está en $HEAD_COMMIT."
        echo "   Regenera la interfaz desde ese commit y súbela antes de actualizar."
        exit 1
    fi
else
    echo "!! Falta public/build/.deploy-commit: sube la interfaz generada desde el commit actual."
    exit 1
fi
if [ -n "$(git -C "$APP_ROOT" status --porcelain)" ]; then
    echo "!! Hay cambios locales sin guardar. Guárdalos o deshazlos antes de actualizar."
    exit 1
fi

# --- 2. Copia recuperable de .env y de la base de datos ---
mkdir -p "$BACKUP_DIR"
cp "$APP_ROOT/.env" "$BACKUP_DIR/.env.$STAMP"
DB_CONNECTION="$(grep -E '^DB_CONNECTION=' "$APP_ROOT/.env" | cut -d= -f2 | tr -d ' "')"
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_FILE="$APP_ROOT/database/database.sqlite"
    test -f "$DB_FILE" && cp "$DB_FILE" "$BACKUP_DIR/database.$STAMP.sqlite" || echo "-- Aviso: no hay database.sqlite que copiar."
fi
echo "-- Copia guardada en $BACKUP_DIR (.env.$STAMP)"

# --- 3. Mantenimiento y actualización del código (sin sobrescribir nada local) ---
"$PHP_BIN" artisan down --render="errors.503" || "$PHP_BIN" artisan down
git -C "$APP_ROOT" fetch origin
git -C "$APP_ROOT" merge --ff-only origin/main

# --- 4. Dependencias de producción (con scripts, para completar package:discover) ---
"$PHP_BIN" "$COMPOSER_PHAR" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
"$PHP_BIN" artisan vendor:publish --tag=laravel-assets --ansi --force

# --- 5. Clave solo si falta (nunca se sustituye una existente con datos) ---
if ! grep -Eq '^APP_KEY=.+' "$APP_ROOT/.env"; then
    "$PHP_BIN" artisan key:generate --ansi --force
else
    echo "-- APP_KEY ya existe: se conserva."
fi

# --- 6. Migraciones (solo añaden, nunca borran) y cachés ---
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

# --- 7. Permisos sanos para escritura (sin 777) ---
chmod 775 "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" 2>/dev/null || echo "-- Aviso: no se pudieron ajustar permisos, revisa storage/ si falla la escritura."

trap - ERR
"$PHP_BIN" artisan up
echo "== Patrimonasa actualizada correctamente =="
