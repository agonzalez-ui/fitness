# FZ FITCR

App de entrenamiento personal: el entrenador arma rutinas y dietas con clics y los clientes siguen su plan desde el celular.

**Stack:** Laravel 13 · Livewire 4 · Tailwind 4 · MySQL · Pest

## Qué hace

**Entrenador** (`/admin`)
- Inicio con lo urgente: membresías por vencer o vencidas, clientes sin rutina, clientes que no registran progreso, mensajes.
- Clientes: alta en un paso (datos + membresía + rutina + dieta), contraseña temporal para enviar por WhatsApp, ficha con historial.
- Membresías: renovación con un clic (encadena fechas para no perder días), ingresos por mes y forma de pago.
- Rutinas y nutrición: armadores con catálogo, arrastrar para ordenar, autoguardado, plantillas reutilizables.
- Mensajes: bandeja compartida entre todas las cuentas de entrenador.
- Catálogos: ejercicios, alimentos (con macros por porción) y planes de membresía.

**Cliente** (`/app`, pensado para el celular)
- "Hoy te toca": el entrenamiento del día con checks, cronómetro de descanso, técnica y video.
- Plan de comidas con calorías y macros calculados.
- Progreso: peso y medidas en segundos, gráficas y fotos privadas (antes / ahora).
- Chat con el entrenador.

## Instalación local

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
```

Configure MySQL en `.env` (`DB_DATABASE=fitness_db`, usuario y clave) y cree la base. Luego:

```bash
php artisan migrate --seed          # tablas + catálogos + plantillas
php artisan storage:link
php artisan serve
```

Cuentas de prueba (solo desarrollo): `php artisan db:seed --class=DemoSeeder` (ver credenciales en `database/seeders/DemoSeeder.php`).

Mientras desarrolla, use `npm run dev` para que Tailwind compile las clases nuevas al instante.

### Tests

Usan la base MySQL `fitness_test` (ver `phpunit.xml`):

```bash
./vendor/bin/pest
```

## Importar los datos del sitio anterior

1. Importe el respaldo SQL del sitio viejo en una base aparte (por defecto `fitness_legacy`; se configura con `LEGACY_DB_*` en `.env`).
2. Con la base nueva vacía y los catálogos cargados:

```bash
php artisan migrate:fresh --seed
php artisan fitness:import-legacy
```

Pasa usuarios (con sus mismas contraseñas), membresías, progreso, fotos, mensajes y planes. El estado de las membresías ya no se guarda: se calcula por fecha.

3. Copie los archivos del servidor viejo:
   - PDFs (`uploads/workout_plans`, `uploads/nutrition_plans`) → `storage/app/public/legacy/`
   - Fotos (`uploads/progress`) → `storage/app/private/legacy/progress/`

## Configuración

| Variable | Para qué | Valor por defecto |
|---|---|---|
| `APP_TIMEZONE` | Zona horaria (qué día "toca" entrenar) | `America/Costa_Rica` |
| `FITNESS_CURRENCY` | Moneda de las membresías (`USD`, `CRC`) | `USD` |
| `LEGACY_DB_DATABASE` | Base del sitio viejo para importar | `fitness_legacy` |

## Despliegue en Hostinger

1. **PHP 8.3 o superior** en hPanel → Avanzado → Configuración de PHP (el sitio viejo usa 7.2).
2. Suba el proyecto **fuera** de `public_html` y apunte el dominio/subdominio a la carpeta `public/`
   (o enlace `public_html` → `public`).
3. En el servidor:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force --seed
php artisan storage:link
php artisan optimize
```

4. En `.env` de producción: `APP_ENV=production`, `APP_DEBUG=false` (el sitio viejo mostraba errores y rutas del servidor al público), `APP_URL=https://…`.
5. Compile los assets en su PC (`npm run build`) y suba la carpeta `public/build`.

## Estructura

- `app/Enums` — valores fijos (objetivo, nivel, grupo muscular, forma de pago, estado de membresía calculado).
- `app/Services` — lógica de negocio: renovar membresías, asignar plantillas.
- `app/Livewire` — pantallas interactivas: armadores, chat, entrenamiento del día.
- `app/Models/Concerns/Auditable.php` — registra altas, cambios y bajas en `activity_logs` sin código extra en los controladores.
- `resources/views/components` — componentes Blade reutilizables (`x-button`, `x-input`, `x-card`, `x-line-chart`, `x-macro-bar`…).
