# SWC — API

API Laravel del panel **WAN Smart Control** (monitorización WAN + gestión de sedes/enlaces).

## Stack

- Laravel 13 + Sanctum
- MySQL (`swc`)

## Arranque

```sh
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API en `http://127.0.0.1:8000/api`.

Usuarios de ejemplo (seeder): contraseña `password`.

## Endpoints principales

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| `POST` | `/auth/login` | no | Login → token Bearer |
| `POST` | `/auth/register` | no | Registro |
| `POST` | `/auth/logout` | sí | Cierra sesión |
| `GET`/`PUT` | `/auth/me` | sí | Perfil |
| `GET` | `/network/summary` | sí | KPIs del dashboard |
| `GET` | `/network/links` | sí | Enlaces (vista dashboard) |
| `GET` | `/alerts` | sí | Alertas recientes |
| `*` | `/sedes` | sí | CRUD de sedes |
| `*` | `/enlaces` | sí | CRUD de enlaces |
| `GET` | `/health` | no | Healthcheck |

## Modelos

- **Sede** — `nombre`, `codigo`, `conectada`
- **Enlace** — origen/destino, `estado` (`operativo` \| `caido` \| `ruta_alternativa`), latencia, pérdida
- **Alerta** — ligada a un enlace
