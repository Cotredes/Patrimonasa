# 🏠 Patrimonasa

### *Esta es mi casa. Aquí están sus papeles. Sé cómo verlos y cómo añadir otro.*

**Patrimonasa** es nuestra aplicación privada y familiar para organizar el patrimonio material
—casas, terrenos, vehículos y otros bienes— y guardar toda su documentación en un solo lugar,
pensada para que una persona mayor la use con tranquilidad, sin miedo a equivocarse.

> ✅ **Estado actual (9 sep 2026):** primera versión funcional disponible en local: acceso
> familiar, bienes, categorías, documentos privados, fotografías, búsqueda, recordatorios,
> favoritos, archivado, papelera, exportación y modo instalable como aplicación web.
> Ver [Estado y pendientes](#-estado-y-pendientes) y `AGENTS.md` → Historial de cambios.

---

## 💛 Qué queremos conseguir

- **Saber qué tenemos:** «Casa del pueblo», «Piso de Madrid», «Coche de papá»… cada bien con su ficha clara, su foto y su información básica.
- **Saber dónde están sus papeles:** escrituras, planos, referencia catastral, ITV, seguros, manuales… visibles desde la ficha, con títulos comprensibles como *«Escrituras de la casa»*.
- **Encontrarlo todo sin esfuerzo:** un buscador grande —_«Busca una casa, un vehículo o un documento»_— que entiende nombres, localidades, matrículas y títulos.
- **Guardar sin miedo:** crear un bien solo con su tipo y su nombre; el resto se completa después. Papelera y archivado protegen de borrados accidentales.
- **Privado por diseño:** solo la familia. Sin registro público, sin enlaces públicos, sin funciones financieras.

**Lo que no es Patrimonasa:** no lleva cuentas, gastos, valoraciones, alquileres, impuestos ni redes sociales. Solo bienes, papeles, fotos y recordatorios sencillos.

## 🧑‍🦳 Diseñada para mi padre

- Texto grande y legible, botones amplios y bien separados, contraste claro.
- Palabras normales en español, iconos siempre con texto, pocas decisiones a la vez.
- Navegación estable: **Inicio · Mis bienes · Documentos · Recordatorios**.
- Mensajes claros: _«Los datos de Casa del pueblo se han guardado»_, _«Se han añadido 3 documentos al coche»_.
- Todo se puede deshacer: archivar, papelera, restaurar. Nada desaparece sin avisar.
- Funciona con ratón, teclado y pantalla táctil, en ordenador, tableta y móvil.

---

## 🛠️ Tecnología

| Pieza | Versión |
|-------|---------|
| Laravel | 13.17 |
| PHP | 8.3 |
| Vite | 8 + `laravel-vite-plugin` |
| Tailwind CSS | v4 (CSS-first, sin `tailwind.config.js`) |
| Base de datos | SQLite (`database/database.sqlite`) |

**Entradas principales:** `routes/web.php` · `resources/css/app.css` · `resources/js/app.js`

## 🚀 Cómo ponerla en marcha (Laragon / Windows)

```powershell
# 1. Primera vez (instala, genera clave, migra y compila)
composer setup

# 2. Desarrollo diario (servidor + cola + logs + vite)
composer dev

# 3. Abrir en el navegador
http://localhost:8000
```

> La primera vez pedirá crear la base de datos SQLite y migrar las tablas de `users`, `cache` y `jobs`.
> No subas nunca tu `database.sqlite` con datos reales: viaja como archivo vacío.

### Verificar que todo va bien

```powershell
composer test          # tests Laravel
npm run build          # compilación frontend
vendor/bin/pint --test # estilo PHP
```

---

## 🌐 Despliegue en Loading (producción)

Dominio: `https://patrimonio.casetashormigon.es` · Código en `/patrimonio.casetashormigon.es/app` · PHP `/ldnwebserver/php83/bin/php` (8.3).

### Primera instalación (una sola vez, por SSH en el servidor)

1. **Raíz de documentos** del subdominio → `/patrimonio.casetashormigon.es/app/public`. Nunca la raíz del proyecto.
2. **`.env`**: copiar `.env.production.example` a `.env`, rellenar `DB_*` con los datos del alojamiento y generar la clave: `/ldnwebserver/php83/bin/php artisan key:generate --force`.
3. **Actualizar**: ejecutar el comando único de abajo. Crea las tablas con las migraciones (sin borrar nada).
4. **Primera cuenta** (privada, por SSH): `/ldnwebserver/php83/bin/php artisan patrimonasa:crear-usuario tu@correo.es --nombre="Tu nombre" --admin`. No existe registro público: nadie puede apropiarse de la cuenta antes que tú.

### Publicar una nueva versión (desde este ordenador)

```powershell
npm run build          # genera public/build (va incluido en Git)
composer test          # 6 pruebas en verde
git add -A; git commit -m "Descripción del cambio"; git push origin main
```

### Actualizar el servidor (único comando, por SSH)

```bash
cd /patrimonio.casetashormigon.es/app && git pull --ff-only origin main && bash scripts/deploy-loading.sh
```

El script comprueba requisitos, valida `.env` (sin modificarlo jamás), guarda copia real de la base de datos, instala desde `composer.lock`, migra y regenera cachés. Si falla, reabre el servicio solo y deja la copia en `../copias-seguridad`; detalles en la cabecera del script.

> Los documentos y fotos solo se sirven a usuarios identificados. El `.env`, la clave, la base de datos y los archivos subidos se conservan en cada actualización.

### Error 419 al entrar (producción)

Causa encontrada: detrás del proxy, PHP recibe la petición en HTTP aunque se visite en HTTPS. Sin corrección, la página generaba el formulario hacia `http://` y el navegador no enviaba la cookie de sesión (segura) al enviarlo, así que cada intento creaba una sesión vacía. Desde esta versión las direcciones se generan en `https` cuando `APP_URL` es `https`, sin confiar en cabeceras del proxy. El CSRF sigue activo y la clave no se toca.

Si el error continuara, hay un diagnóstico temporal (`/diag-sesion`) que no muestra secretos:

1. Por SSH, genera un token y añádelo al `.env` (nunca uses `config:cache` desde SSH):
   `/ldnwebserver/php83/bin/php -r 'echo bin2hex(random_bytes(24)).PHP_EOL;'`
   y añade la línea `DIAGNOSTIC_TOKEN=valor-generado`.
2. Abre `https://patrimonio.casetashormigon.es/diag-sesion?t=valor-generado` dos veces seguidas y anota lo que indica cada fila.
3. Para retirarlo: borra la línea del `.env` (se desactiva al instante) y avísame para eliminarlo del código.

Si esa página diera 404 aun con el token correcto, hay una vieja caché de configuración con otras rutas: borra `bootstrap/cache/config.php` en el servidor.

---

## ✅ Estado y pendientes

### Disponible

- Acceso privado con cuentas familiares creadas por SSH; sin registro público.
- Inicio con buscador global, categorías y accesos rápidos.
- Crear, editar, marcar como favorito y archivar bienes sin formularios interminables.
- Categorías creadas y archivadas desde la aplicación.
- Subida múltiple de documentos y fotos desde ordenador o móvil, conservando el archivo original.
- Documentos protegidos por sesión, consulta en el navegador, descarga y exportación ZIP por bien.
- Búsqueda por nombre, datos del bien, título, tipo y nombre del archivo.
- Recordatorios de fechas, papelera y restauración.
- Manifest y service worker básicos para instalar Patrimonasa desde Chrome como aplicación.
- Firma cálida y visible en todas las pantallas: **«Hecho por Ángel Muñoz con mucho cariño»**.
- Flecha «Volver» grande y clara en todas las pantallas internas, con alternativa directa si se entra por enlace.

### Pendiente de una siguiente iteración

- Pantalla de administración de usuarios y alta de familiares desde el administrador.
- Duplicados/versiones de documentos y relaciones entre bienes.
- Exportación completa de toda la familia, con copia restaurable además del ZIP individual.
- Iconos gráficos propios y pruebas automatizadas específicas de cada recorrido.

## 🗺️ Roadmap hacia la versión familiar completa

Queremos recorridos completos, no botones de adorno. Este es el orden previsto:

- [x] **Acceso familiar:** entrar con cuenta propia, zona privada y documentos protegidos.
- [x] **Mis bienes:** categorías iniciales, crear bien solo con tipo + nombre y editar después.
- [x] **Ficha del bien:** foto principal, información, documentos, fotos, notas y recordatorios.
- [x] **Documentos:** subida múltiple, título comprensible, tipo flexible, importante, ver/descargar y papelera.
- [x] **Fotos:** varias por bien, principal, vista ampliada y eliminación independiente.
- [x] **Buscador único:** bienes y documentos por sus datos principales.
- [x] **Categorías vivas:** crear, renombrar y archivar sin perder bienes.
- [x] **Archivar y papelera:** archivar conserva papeles, restaurar y borrado definitivo admin.
- [x] **Recordatorios sencillos:** fecha, bien relacionado y completar.
- [x] **Exportar:** descargar un bien con sus datos, documentos y fotos en ZIP.

Cuando cada punto funcione de principio a fin —crear, guardar, volver a abrir, buscar, archivar y restaurar— lo marcaremos aquí como ✅.

## 🧪 Cómo probarlo (cuando haya algo que probar)

1. Entra en `http://localhost:8000`.
2. En la primera visita pulsa **«Crear cuenta familiar»**. La primera cuenta queda como administradora.
3. Pulsa **«Añadir un bien»**, elige vivienda y escribe *«Casa del pueblo»*.
4. Añade una foto y la localidad desde **Editar información**.
5. Abre la ficha → **«Añadir documento»** → sube un PDF y llámalo *«Escrituras»*.
6. Busca *«pueblo»* en el inicio: debe aparecer la casa y su escritura.
7. Desde Chrome usa **Instalar Patrimonasa** o **Añadir a pantalla de inicio** para abrirla como aplicación.

En el entorno local ya existe una cuenta familiar preparada para Pedro. Las credenciales se han comunicado fuera del código y nunca se guardan en este repositorio.

> Si algún paso falla o un botón no hace nada, es un error: avísanos. No presentamos funciones a medias como terminadas.

---

## 📁 Estructura (resumen)

```
app/            Lógica (modelos, controladores)
routes/web.php  Rutas principales
resources/      Vistas Blade + CSS + JS (Vite)
database/       Migraciones + SQLite local
tests/          Tests Feature de recorridos críticos
AGENTS.md       Guía viva para desarrolladores/agentes + historial
```

## 🔒 Privacidad

Repositorio privado familiar. Sin registro abierto, sin compartir público de escrituras.
Las vistas previas y descargas exigirán haber entrado con cuenta.

## 📝 Cambios y diario

Cada cambio queda apuntado el mismo día en `AGENTS.md` (tabla *Historial de cambios*) y en este `README`
(*Estado*, *Cómo probarlo* o *Roadmap*). Sin tecnicismos, con tono claro y cálido.

---

<p align="center">Hecho con calma en casa, para que los papeles de la familia estén siempre a mano. 🏠</p>
