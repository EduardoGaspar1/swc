<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { Pencil, Plus, Trash2, X } from '@lucide/vue'
import { useFlashMessage } from '@/composables/useFlashMessage'
import { useEnlacesStore } from '@/stores/useEnlacesStore'
import { useSedesStore } from '@/stores/useSedesStore'

const enlacesStore = useEnlacesStore()
const sedesStore = useSedesStore()
const { enlaces } = storeToRefs(enlacesStore)
const { sedes } = storeToRefs(sedesStore)
const { message: success, flash: flashSuccess, clear: clearSuccess } = useFlashMessage()

const statusLabel = {
  operativo: 'Operativo',
  caido: 'Caído',
  ruta_alternativa: 'Ruta alternativa',
}

const loading = ref(true)
const saving = ref(false)
const listError = ref('')
const formError = ref('')
const fieldErrors = ref({})
const editingId = ref(null)
const modalOpen = ref(false)

const form = reactive({
  sedeOrigenId: '',
  sedeDestinoId: '',
  estado: 'operativo',
  latenciaMs: '',
  perdidaPct: 0,
})

const canCreate = computed(() => sedes.value.length >= 2)
const modalTitle = computed(() => (editingId.value ? 'Editar enlace' : 'Nuevo enlace'))
const submitLabel = computed(() => {
  if (saving.value) return 'Guardando…'
  return editingId.value ? 'Guardar cambios' : 'Crear enlace'
})

function resetForm() {
  editingId.value = null
  form.sedeOrigenId = ''
  form.sedeDestinoId = ''
  form.estado = 'operativo'
  form.latenciaMs = ''
  form.perdidaPct = 0
  fieldErrors.value = {}
  formError.value = ''
}

function openCreate() {
  resetForm()
  clearSuccess()
  modalOpen.value = true
}

function openEdit(enlace) {
  editingId.value = enlace.id
  form.sedeOrigenId = String(enlace.sedeOrigenId)
  form.sedeDestinoId = String(enlace.sedeDestinoId)
  form.estado = enlace.estado
  form.latenciaMs = enlace.latenciaMs == null ? '' : String(enlace.latenciaMs)
  form.perdidaPct = enlace.perdidaPct
  formError.value = ''
  clearSuccess()
  fieldErrors.value = {}
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  resetForm()
}

function extractError(err) {
  const data = err.response?.data
  if (data?.errors) {
    fieldErrors.value = Object.fromEntries(
      Object.entries(data.errors).map(([key, messages]) => [key, messages?.[0] ?? '']),
    )
    return ''
  }
  return data?.message || 'No se pudo completar la operación.'
}

function buildPayload() {
  return {
    sedeOrigenId: Number(form.sedeOrigenId),
    sedeDestinoId: Number(form.sedeDestinoId),
    estado: form.estado,
    latenciaMs: form.latenciaMs === '' ? null : Number(form.latenciaMs),
    perdidaPct: Number(form.perdidaPct),
  }
}

async function loadAll() {
  loading.value = true
  listError.value = ''
  try {
    await Promise.all([enlacesStore.fetch(), sedesStore.fetch()])
  } catch {
    listError.value = 'No se pudieron cargar los enlaces.'
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  saving.value = true
  formError.value = ''
  clearSuccess()
  fieldErrors.value = {}

  try {
    if (editingId.value) {
      const { data } = await enlacesStore.update({ id: editingId.value, ...buildPayload() })
      flashSuccess(data.message)
    } else {
      const { data } = await enlacesStore.store(buildPayload())
      flashSuccess(data.message)
    }
    closeModal()
    await loadAll()
  } catch (err) {
    formError.value = extractError(err)
  } finally {
    saving.value = false
  }
}

async function onDelete(enlace) {
  const ok = window.confirm(`¿Eliminar el enlace "${enlace.nombre}"?`)
  if (!ok) return

  listError.value = ''
  clearSuccess()
  try {
    const { data } = await enlacesStore.delete(enlace)
    if (editingId.value === enlace.id) closeModal()
    flashSuccess(data.message)
    await loadAll()
  } catch (err) {
    listError.value = extractError(err)
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && modalOpen.value) closeModal()
}

onMounted(() => {
  loadAll()
  document.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <main class="page">
    <header class="page-header">
      <div>
        <h1 class="page-title">Enlaces</h1>
        <p class="page-sub">Gestiona las conexiones entre sedes.</p>
      </div>
      <button
        type="button"
        class="btn primary"
        :disabled="!canCreate"
        :title="canCreate ? undefined : 'Necesitas al menos dos sedes'"
        @click="openCreate"
      >
        <Plus :size="16" :stroke-width="2" aria-hidden="true" />
        Crear enlace
      </button>
    </header>

    <p v-if="success" class="banner success" role="status">{{ success }}</p>
    <p v-if="listError" class="banner error" role="alert">{{ listError }}</p>
    <p v-if="!loading && !canCreate" class="banner warn">
      Necesitas al menos dos sedes para crear un enlace.
    </p>

    <section class="panel">
      <p v-if="loading" class="status">Cargando enlaces…</p>
      <div v-else-if="enlaces.length === 0" class="empty">
        <p class="empty-title">Aún no hay enlaces</p>
        <p class="empty-sub">Crea el primero con el botón de arriba.</p>
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Enlace</th>
              <th>Latencia</th>
              <th>Pérdida</th>
              <th>Estado</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="enlace in enlaces" :key="enlace.id">
              <td>{{ enlace.nombre }}</td>
              <td>{{ enlace.latenciaMs == null ? '—' : `${enlace.latenciaMs} ms` }}</td>
              <td>{{ enlace.perdidaPct }} %</td>
              <td>
                <span class="badge" :class="`status-${enlace.estado}`">
                  {{ statusLabel[enlace.estado] ?? enlace.estado }}
                </span>
              </td>
              <td class="row-actions">
                <button
                  type="button"
                  class="icon-btn"
                  title="Editar"
                  aria-label="Editar enlace"
                  @click="openEdit(enlace)"
                >
                  <Pencil :size="16" :stroke-width="1.75" />
                </button>
                <button
                  type="button"
                  class="icon-btn danger"
                  title="Eliminar"
                  aria-label="Eliminar enlace"
                  @click="onDelete(enlace)"
                >
                  <Trash2 :size="16" :stroke-width="1.75" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <Teleport to="body">
      <div
        v-if="modalOpen"
        class="modal-backdrop"
        @click.self="closeModal"
      >
        <div
          class="modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="enlace-modal-title"
        >
          <div class="modal-header">
            <h2 id="enlace-modal-title" class="modal-title">{{ modalTitle }}</h2>
            <button
              type="button"
              class="icon-btn"
              title="Cerrar"
              aria-label="Cerrar"
              @click="closeModal"
            >
              <X :size="18" :stroke-width="1.75" />
            </button>
          </div>

          <form class="form" @submit.prevent="onSubmit">
            <label class="field">
              <span>Sede origen</span>
              <select v-model="form.sedeOrigenId" required>
                <option value="" disabled>Selecciona una sede</option>
                <option v-for="sede in sedes" :key="`o-${sede.id}`" :value="String(sede.id)">
                  {{ sede.nombre }}
                </option>
              </select>
              <span v-if="fieldErrors.sedeOrigenId" class="field-error">{{ fieldErrors.sedeOrigenId }}</span>
            </label>

            <label class="field">
              <span>Sede destino</span>
              <select v-model="form.sedeDestinoId" required>
                <option value="" disabled>Selecciona una sede</option>
                <option v-for="sede in sedes" :key="`d-${sede.id}`" :value="String(sede.id)">
                  {{ sede.nombre }}
                </option>
              </select>
              <span v-if="fieldErrors.sedeDestinoId" class="field-error">{{ fieldErrors.sedeDestinoId }}</span>
            </label>

            <label class="field">
              <span>Estado</span>
              <select v-model="form.estado" required>
                <option value="operativo">Operativo</option>
                <option value="caido">Caído</option>
                <option value="ruta_alternativa">Ruta alternativa</option>
              </select>
              <span v-if="fieldErrors.estado" class="field-error">{{ fieldErrors.estado }}</span>
            </label>

            <label class="field">
              <span>Latencia (ms)</span>
              <input v-model="form.latenciaMs" type="number" min="0" max="60000" placeholder="Opcional" />
              <span v-if="fieldErrors.latenciaMs" class="field-error">{{ fieldErrors.latenciaMs }}</span>
            </label>

            <label class="field">
              <span>Pérdida (%)</span>
              <input v-model.number="form.perdidaPct" type="number" min="0" max="100" required />
              <span v-if="fieldErrors.perdidaPct" class="field-error">{{ fieldErrors.perdidaPct }}</span>
            </label>

            <p v-if="formError" class="form-error" role="alert">{{ formError }}</p>

            <div class="modal-actions">
              <button class="btn ghost" type="button" @click="closeModal">Cancelar</button>
              <button class="btn primary" type="submit" :disabled="saving">
                {{ submitLabel }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </main>
</template>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.page-title {
  color: var(--swc-heading);
  font-size: 1.25rem;
  font-weight: 600;
}

.page-sub {
  color: var(--swc-text-muted);
  font-size: 0.92rem;
  margin-top: 0.2rem;
}

.banner {
  font-size: 0.9rem;
  padding: 0.7rem 0.9rem;
  border-radius: var(--swc-radius-sm);
  border: 1px solid transparent;
}

.banner.success {
  color: var(--swc-success);
  background: var(--swc-success-bg);
}

.banner.error {
  color: var(--swc-danger);
  background: var(--swc-danger-bg);
}

.banner.warn {
  color: var(--swc-warning);
  background: var(--swc-warning-bg);
}

.panel {
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  padding: 1.25rem 1.35rem 1.4rem;
  box-shadow: var(--swc-shadow);
}

.form {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  color: var(--swc-text-muted);
  font-size: 0.9rem;
}

.field input,
.field select {
  appearance: none;
  width: 100%;
  border: 1px solid var(--swc-border);
  background: var(--swc-bg);
  color: var(--swc-text);
  border-radius: var(--swc-radius-sm);
  padding: 0.65rem 0.8rem;
  font: inherit;
  outline: none;
}

.field input:focus,
.field select:focus {
  border-color: var(--swc-info);
}

.btn {
  appearance: none;
  border: 0;
  border-radius: var(--swc-radius-sm);
  padding: 0.65rem 0.95rem;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.btn.primary {
  background: var(--swc-info);
  color: #0b1520;
}

.btn.ghost {
  background: transparent;
  border: 1px solid var(--swc-border);
  color: var(--swc-text);
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.icon-btn {
  appearance: none;
  border: 1px solid transparent;
  background: transparent;
  color: var(--swc-text-muted);
  width: 2rem;
  height: 2rem;
  border-radius: var(--swc-radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.icon-btn:hover {
  color: var(--swc-heading);
  background: var(--swc-bg-soft);
  border-color: var(--swc-border);
}

.icon-btn.danger:hover {
  color: var(--swc-danger);
  background: var(--swc-danger-bg);
  border-color: transparent;
}

.row-actions {
  display: flex;
  gap: 0.25rem;
  justify-content: flex-end;
}

.col-actions {
  text-align: right;
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
  padding: 0.75rem 0.75rem 0.75rem 0;
  border-bottom: 1px solid var(--swc-border);
  color: var(--swc-text);
  vertical-align: middle;
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

.status,
.empty {
  color: var(--swc-text-muted);
  text-align: center;
  padding: 1.5rem 0.5rem;
}

.empty-title {
  color: var(--swc-heading);
  margin-bottom: 0.35rem;
}

.field-error,
.form-error {
  color: var(--swc-danger);
  font-size: 0.85rem;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  z-index: 80;
}

.modal {
  width: 100%;
  max-width: 460px;
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  box-shadow: var(--swc-shadow);
  padding: 1.2rem 1.25rem 1.35rem;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 1.1rem;
}

.modal-title {
  color: var(--swc-heading);
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.55rem;
  margin-top: 0.35rem;
}

@media (max-width: 560px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .page-header .btn {
    align-self: flex-start;
  }
}
</style>
