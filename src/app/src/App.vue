<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import UserMenu from '@/components/UserMenu.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()

const showChrome = computed(() => route.path !== '/login' && auth.isAuthenticated)
</script>

<template>
  <div class="app-shell" :class="{ 'is-login': route.path === '/login' }">
    <header v-if="showChrome" class="app-header">
      <RouterLink class="brand" to="/">
        <span class="brand-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
            <circle cx="12" cy="8" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="8" cy="14" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="16" cy="14" r="1.25" fill="currentColor" stroke="none" />
          </svg>
        </span>
        <span class="brand-name">WAN Smart Control</span>
      </RouterLink>

      <UserMenu />
    </header>

    <RouterView />
  </div>
</template>

<style scoped>
.app-shell {
  padding-top: 1.25rem;
}

.app-shell.is-login {
  padding-top: 0;
}

.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  color: var(--swc-heading);
  font-weight: 600;
  font-size: 1.05rem;
  letter-spacing: 0.01em;
}

.brand-icon {
  display: inline-flex;
  color: var(--swc-text-muted);
}

@media (max-width: 560px) {
  .app-header {
    flex-wrap: wrap;
  }
}
</style>
