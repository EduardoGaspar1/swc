<script setup>
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import AlertsList from '@/components/dashboard/AlertsList.vue'
import KpiCard from '@/components/dashboard/KpiCard.vue'
import LinksTable from '@/components/dashboard/LinksTable.vue'
import { useDashboardStore } from '@/stores/useDashboardStore'

const dashboardStore = useDashboardStore()
const { summary, links, alerts } = storeToRefs(dashboardStore)

const loading = ref(true)
const error = ref(null)

const sedesValue = computed(
  () => `${summary.value.sedesConectadas} / ${summary.value.sedesTotal}`,
)
const enlacesValue = computed(
  () => `${summary.value.enlacesActivos} / ${summary.value.enlacesTotal}`,
)
const latenciaValue = computed(() =>
  summary.value.latenciaMediaMs == null ? '—' : `${summary.value.latenciaMediaMs} ms`,
)
const alertasValue = computed(() => String(summary.value.alertasActivas))
const alertasAccent = computed(() => (summary.value.alertasActivas > 0 ? 'danger' : ''))

onMounted(async () => {
  try {
    await dashboardStore.fetch()
  } catch {
    error.value = 'No se pudo cargar el dashboard. Comprueba la conexión con la API.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <main class="dashboard">
    <p v-if="loading" class="status">Cargando dashboard…</p>
    <p v-else-if="error" class="status error">{{ error }}</p>

    <template v-else>
      <section class="kpi-grid" aria-label="Resumen de la red">
        <KpiCard label="Sedes conectadas" :value="sedesValue" />
        <KpiCard label="Enlaces activos" :value="enlacesValue" />
        <KpiCard label="Latencia media" :value="latenciaValue" />
        <KpiCard label="Alertas activas" :value="alertasValue" :accent="alertasAccent" />
      </section>

      <LinksTable :links="links" />
      <AlertsList :alerts="alerts" />
    </template>
  </main>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.status {
  color: var(--swc-text-muted);
  padding: 2rem 0;
  text-align: center;
}

.status.error {
  color: var(--swc-danger);
}

@media (max-width: 900px) {
  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 520px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
}
</style>
