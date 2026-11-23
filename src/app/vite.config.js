import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import VueRouter from 'vue-router/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    VueRouter({
      routesFolder: 'src/pages',
      dts: 'src/route-map.d.ts',
      extendRoute(route) {
        // Novas páginas ficam protegidas por defeito.
        // Login é a única rota pública (guest).
        if (route.fullPath === '/login') {
          route.addToMeta({ guest: true })
        } else {
          route.addToMeta({ requiresAuth: true })
        }
      },
    }),
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})
