<script setup>
defineProps({
  links: { type: Array, default: () => [] },
})

const statusLabel = {
  operativo: 'Operativo',
  caido: 'Caído',
  ruta_alternativa: 'Ruta alternativa',
}
</script>

<template>
  <section class="panel">
    <h2 class="panel-title">Estado de los enlaces</h2>

    <div v-if="links.length === 0" class="empty">
      <p class="empty-title">Aún no hay enlaces registrados</p>
      <p class="empty-sub">
        Cuando la monitorización esté activa, verás aquí el estado de cada enlace.
      </p>
    </div>

    <div v-else class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Enlace</th>
            <th>Latencia</th>
            <th>Pérdida</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(link, index) in links" :key="link.id ?? index">
            <td>{{ link.nombre }}</td>
            <td>{{ link.latenciaMs == null ? '—' : `${link.latenciaMs} ms` }}</td>
            <td>{{ link.perdidaPct }} %</td>
            <td>
              <span class="badge" :class="`status-${link.estado}`">
                {{ statusLabel[link.estado] ?? link.estado }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
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
  padding: 2.25rem 1rem;
}

.empty-title {
  color: var(--swc-heading);
  font-weight: 500;
  margin-bottom: 0.4rem;
}

.empty-sub {
  color: var(--swc-text-muted);
  font-size: 0.9rem;
  max-width: 28rem;
  margin: 0 auto;
}

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

th {
  color: var(--swc-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  padding: 0.55rem 0.75rem 0.75rem 0;
  border-bottom: 1px solid var(--swc-border);
}

td {
  padding: 0.9rem 0.75rem 0.9rem 0;
  border-bottom: 1px solid var(--swc-border);
  color: var(--swc-text);
}

tr:last-child td {
  border-bottom: none;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 500;
}

.status-operativo {
  color: var(--swc-success);
  background: var(--swc-success-bg);
}

.status-caido {
  color: var(--swc-danger);
  background: var(--swc-danger-bg);
}

.status-ruta_alternativa {
  color: var(--swc-info);
  background: var(--swc-info-bg);
}
</style>
