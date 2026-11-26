# SWC — Frontend

Aplicación Vue 3 (Vite) del panel **WAN Smart Control**.

## Stack

- Vue 3 + Pinia + Vue Router 5 (file-based routing)
- Axios
- Tema oscuro propio (`--swc-*`)

## Arranque

```sh
cp .env.example .env   # VITE_API_URL=http://127.0.0.1:8000/api
npm install
npm run dev
```

App en `http://localhost:5173`. La API debe estar en marcha (`php artisan serve` en `src/api`).

## Estructura de páginas

Las rutas se generan automáticamente desde `src/pages`:

| Ruta | Fichero |
|---|---|
| `/` | `src/pages/index.vue` |
| `/login` | `src/pages/login/index.vue` |
| `/settings` | `src/pages/settings/index.vue` |
| `/sedes` | `src/pages/sedes/index.vue` |
| `/enlaces` | `src/pages/enlaces/index.vue` |

Auth: token Sanctum en cookie `swc_token`, enviado como `Authorization: Bearer …`.

## Scripts

```sh
npm run dev
npm run build
npm run lint
```
