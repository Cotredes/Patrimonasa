# 🏠 Patrimonasa — AGENTS.md

> **Archivo familiar privado para saber qué tenemos, dónde está y dónde están sus papeles.**
> Laravel 13 · PHP 8.3 · Vite 8 · Tailwind CSS v4 · SQLite. Diseñado con prioridad absoluta: **que una persona mayor lo use sin miedo.**

Última actualización: **9 de septiembre de 2026**

---

## 1. Visión del producto (no perder de vista)

- Bienes materiales + documentación, **nada financiero** (sin cuentas, gastos, valoraciones ni rentabilidades).
- Recorridos críticos: crear bien en 2 campos → añadir foto/docs → encontrarlo por buscador → archivar/restaurar sin perder papeles.
- Accesibilidad senior-first: texto grande, botones amplios, español llano, mensajes tipo _«Los datos de Casa del pueblo se han guardado»_, navegación estable Inicio / Mis bienes / Documentos / Recordatorios.

## 2. Stack y puntos de entrada

| Capa | Detalle |
|------|---------|
| Backend | `routes/web.php`, `routes/console.php`, `app/Models`, `app/Http`, `app/Providers`, `config/` |
| Frontend | Vite 8 + Tailwind v4 CSS-first. Entradas `resources/css/app.css` + `resources/js/app.js` vía `vite.config.js` |
| Estilos | `@import 'tailwindcss'` + `@theme` en `resources/css/app.css`. **No crear `tailwind.config.js`** |
| BD | SQLite `database/database.sqlite` (`DB_CONNECTION=sqlite`). Solo 3 migraciones: `users`, `cache`, `jobs` |
| Estado actual | Esqueleto Laravel. Solo `GET / → welcome`. Sin auth, sin dominio, sin Boost |

> `laravel/boost` **no está instalado**. No asumas sus herramientas.

## 3. Comandos exactos (desde la raíz)

```powershell
# Primera vez
composer setup        # install + key:generate + migrate + npm install --ignore-scripts + build

# Desarrollo
composer dev          # php artisan dev (servidor + cola + logs + vite)
npm run dev           # solo frontend
npm run build         # build frontend (obligatorio tras install)

# Calidad
composer test                         # config:clear + php artisan test
php artisan test --filter=NombreTest  # un solo test
vendor/bin/pint --test                # lint (fix: vendor/bin/pint)
```

## 4. Convenciones del repo

- Español llano en UI y mensajes. Fechas y unidades formato España.
- Formularios: solo `nombre` + `categoría` obligatorios al crear un bien; el resto después.
- Ficheros: nunca borrar automáticamente; papelera → restaurar → borrado definitivo (solo admin).
- Tailwind v4, Blade + Vite. Sin framework JS salvo necesidad justificada.
- Tests Feature para cada recorrido crítico antes de darlo por terminado.

## 5. Gotchas verificados

- `SESSION_DRIVER`, `CACHE_STORE` y `QUEUE_CONNECTION` son `database` → **migrar antes** de probar login/sesión/cola. Los tests ya usan `array/sync` + SQLite `:memory:` (`phpunit.xml`).
- `.npmrc` tiene `ignore-scripts=true` → `npm install` no hace build; ejecuta `npm run build` siempre después.
- `.env` está gitignored, `APP_URL=http://localhost:8000`. `database/*.sqlite` viaja como placeholder vacío — **jamás commitear datos reales**.
- `node_modules/`, `public/build`, `public/hot` son artefactos gitignored (Node 22 + Vite 8).
- Sin CI, sin `opencode.json`, sin hooks. Verificación manual: `composer test` + `npm run build`.
- Remoto: `origin https://github.com/Cotredes/Patrimonasa.git`, rama `main` con upstream `origin/main`. Local y remoto nacieron con historiales divergentes — no hacer push/pull a ciegas.

## 6. 📝 Norma obligatoria de documentación

> **Cada cambio de código, esquema, UX o comandos debe quedar apuntado el mismo día en `AGENTS.md` (sección 7) y en `README.md` (sección correspondiente), de forma bonita y profesional.**

- En `AGENTS.md`: añade una fila a _Historial de cambios_ con fecha, ámbito y qué cambió.
- En `README.md`: actualiza _Estado_, _Cómo probarlo_ o _Roadmap_ según toque. Nada de botones muertos ni funciones presentadas como hechas sin estarlo.
- Tono: claro, cálido, sin tecnicismos innecesarios.

## 7. Historial de cambios

| Fecha | Ámbito | Cambio |
|-------|--------|--------|
| 2026-09-09 | Repo | Conexión verificada con `https://github.com/Cotredes/Patrimonasa.git` (`fetch` OK, `main` → `origin/main`). Historiales divergentes conservados sin forzar. |
| 2026-09-09 | Docs | `AGENTS.md` y `README.md` reescritos en formato profesional Patrimonasa + norma de documentación continua. |
| 2026-09-09 | App | Primera versión funcional: auth familiar, bienes, categorías, documentos/fotos privados, búsqueda, recordatorios, favoritos, archivado, papelera, exportación ZIP y PWA básica. Pendientes documentados en `README.md`. |
| 2026-09-09 | Calidad | Tests Feature para alta/subida/restauración, migraciones ejecutadas, vistas compiladas, Pint y Vite verificados correctamente. |
| 2026-09-09 | UX | Formularios de documentos editables, primera cuenta con rol administrador y categorías inicializadas aunque se entre directamente por una ruta secundaria. |
| 2026-09-09 | Datos locales | Cuenta familiar de Pedro creada en la base de datos local. La contraseña no se guarda en el repositorio ni en esta guía. |
| 2026-09-09 | UX | Firma visible y animada en el pie de la interfaz: «Hecho por Ángel Muñoz con mucho cariño», con respeto por la reducción de movimiento. |
| 2026-09-09 | UX | Navegación con flecha «Volver» grande y visible en todas las pantallas internas, corrección de errores de diseño (cabeceras móviles, acciones de fotos táctiles, desplegable de categorías y resultados de búsqueda con bienes y documentos). |
| 2026-09-09 | Repo | Primera subida completa a `origin/main`: historiales unificados conservando ambos inicios y código Patrimonasa publicado. |
| 2026-09-09 | Despliegue | Preparación Loading (`patrimonio.casetashormigon.es`): build Vite verificado, registro cerrado tras la primera cuenta, plantilla `.env.production.example` y script `scripts/deploy-loading.sh` con copia, migraciones y cachés. Sin acceso SSH/SFTP configurado ni BD de producción todavía. |
