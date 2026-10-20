<script setup>
defineProps({
  alerts: { type: Array, default: () => [] },
})
</script>

<template>
  <section class="panel">
    <h2 class="panel-title">Alertas recientes</h2>

    <div v-if="alerts.length === 0" class="empty">
      <p class="empty-title">No hay alertas recientes</p>
      <p class="empty-sub">Todo en calma por ahora.</p>
    </div>

    <ul v-else class="alerts">
      <li v-for="(alert, index) in alerts" :key="alert.id ?? index" class="alert-row">
        <span class="icon" :class="`sev-${alert.severidad}`" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
            <path
              d="M12 3.5 2.8 19.2a1 1 0 0 0 .87 1.5h16.66a1 1 0 0 0 .87-1.5L12 3.5Zm0 5.2a.9.9 0 0 1 .9.9v4.2a.9.9 0 0 1-1.8 0V9.6a.9.9 0 0 1 .9-.9Zm0 8.4a1.05 1.05 0 1 1 0-2.1 1.05 1.05 0 0 1 0 2.1Z"
            />
          </svg>
        </span>
        <p class="message">{{ alert.mensaje }}</p>
        <time class="time">{{ alert.hora }}</time>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.panel {
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  padding: 1.25rem 1.35rem 1.4rem;
  box-shadow: var(--swc-shadow);
}

.panel-title {
  color: var(--swc-heading);
  font-size: 1.05rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.empty {
  text-align: center;
  padding: 2rem 1rem;
}

.empty-title {
  color: var(--swc-heading);
  font-weight: 500;
  margin-bottom: 0.35rem;
}

.empty-sub {
  color: var(--swc-text-muted);
  font-size: 0.9rem;
}

.alerts {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.alert-row {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 0.75rem;
  align-items: start;
}

.icon {
  display: inline-flex;
  margin-top: 0.1rem;
}

.sev-critical {
  color: var(--swc-danger);
}

.sev-warning {
  color: var(--swc-warning);
}

.message {
  color: var(--swc-text);
  font-size: 0.95rem;
}

.time {
  color: var(--swc-text-muted);
  font-size: 0.9rem;
  white-space: nowrap;
}
</style>
