<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <div class="brand">
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
      </div>

      <div class="user">
        <span class="user-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="8" r="3.5" />
            <path d="M5 19.5c1.5-3 4-4.5 7-4.5s5.5 1.5 7 4.5" />
          </svg>
        </span>
        <span>Administrador</span>
      </div>
    </header>

    <RouterView />
  </div>
</template>

<style scoped>
.app-shell {
  padding-top: 1.25rem;
}

.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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

.user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--swc-text-muted);
  font-size: 0.95rem;
}

.user-icon {
  display: inline-flex;
  opacity: 0.9;
}
</style>
