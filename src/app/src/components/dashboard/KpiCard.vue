<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: String, required: true },
  accent: { type: String, default: '' },
})
</script>

<template>
  <article class="kpi-card">
    <p class="kpi-label">{{ label }}</p>
    <p class="kpi-value" :class="accent ? `accent-${accent}` : null">{{ value }}</p>
  </article>
</template>

<style scoped>
.kpi-card {
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  padding: 1.15rem 1.25rem;
  box-shadow: var(--swc-shadow);
}

.kpi-label {
  color: var(--swc-text-muted);
  font-size: 0.9rem;
  margin-bottom: 0.55rem;
}

.kpi-value {
  color: var(--swc-heading);
  font-size: 1.65rem;
  font-weight: 600;
  letter-spacing: 0.01em;
}

.accent-danger {
  color: var(--swc-danger);
}
</style>
