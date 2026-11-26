<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { Pencil, Plus, Trash2, X } from '@lucide/vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useSedesStore } from '@/stores/useSedesStore'

const sedesStore = useSedesStore()
const { sedes } = storeToRefs(sedesStore)

const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const listError = ref('')
const formError = ref('')
const success = ref('')
const fieldErrors = ref({})
const editingId = ref(null)
const modalOpen = ref(false)
const confirmOpen = ref(false)
const pendingDelete = ref(null)

const confirmMessage = computed(() => {
  const nombre = pendingDelete.value?.nombre ?? ''
  return `¿Eliminar la sede "${nombre}"? También se eliminarán sus enlaces asociados.`
})

const form = reactive({
  nombre: '',
  codigo: '',
  conectada: true,
})

const modalTitle = computed(() => (editingId.value ? 'Editar sede' : 'Nueva sede'))
const submitLabel = computed(() => {
  if (saving.value) return 'Guardando…'
  return editingId.value ? 'Guardar cambios' : 'Crear sede'
})

function resetForm() {
  editingId.value = null
  form.nombre = ''
  form.codigo = ''
  form.conectada = true
  fieldErrors.value = {}
  formError.value = ''
}

function openCreate() {
  resetForm()
  success.value = ''
  modalOpen.value = true
}

function openEdit(sede) {
  editingId.value = sede.id
  form.nombre = sede.nombre
  form.codigo = sede.codigo
  form.conectada = sede.conectada
  formError.value = ''
  success.value = ''
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

async function loadSedes() {
  loading.value = true
  listError.value = ''
  try {
    await sedesStore.fetch()
  } catch {
    listError.value = 'No se pudieron cargar las sedes.'
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  saving.value = true
  formError.value = ''
  success.value = ''
  fieldErrors.value = {}

  const payload = {
    nombre: form.nombre.trim(),
    codigo: form.codigo.trim(),
    conectada: form.conectada,
  }

  try {
    if (editingId.value) {
      const { data } = await sedesStore.update({ id: editingId.value, ...payload })
      success.value = data.message
    } else {
      const { data } = await sedesStore.store(payload)
      success.value = data.message
    }
    closeModal()
    await loadSedes()
  } catch (err) {
    formError.value = extractError(err)
  } finally {
    saving.value = false
  }
}

function askDelete(sede) {
  pendingDelete.value = sede
  confirmOpen.value = true
}

function cancelDelete() {
  confirmOpen.value = false
  pendingDelete.value = null
}

async function confirmDelete() {
  const sede = pendingDelete.value
  if (!sede) return

  deleting.value = true
  listError.value = ''
  success.value = ''
  try {
    const { data } = await sedesStore.delete(sede)
    if (editingId.value === sede.id) closeModal()
    success.value = data.message
    confirmOpen.value = false
    pendingDelete.value = null
    await loadSedes()
  } catch (err) {
    listError.value = extractError(err)
    confirmOpen.value = false
    pendingDelete.value = null
  } finally {
    deleting.value = false
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && modalOpen.value && !confirmOpen.value) closeModal()
}

onMounted(() => {
  loadSedes()
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
        <h1 class="page-title">Sedes</h1>
        <p class="page-sub">Gestiona las sedes de la red WAN.</p>
      </div>
      <button type="button" class="btn primary" @click="openCreate">
        <Plus :size="16" :stroke-width="2" aria-hidden="true" />
        Crear sede
      </button>
    </header>

    <p v-if="success" class="banner success" role="status">{{ success }}</p>
    <p v-if="listError" class="banner error" role="alert">{{ listError }}</p>

    <section class="panel">
      <p v-if="loading" class="status">Cargando sedes…</p>
      <div v-else-if="sedes.length === 0" class="empty">
        <p class="empty-title">Aún no hay sedes</p>
        <p class="empty-sub">Crea la primera sede con el botón de arriba.</p>
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Código</th>
              <th>Estado</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sede in sedes" :key="sede.id">
              <td>{{ sede.nombre }}</td>
              <td><code>{{ sede.codigo }}</code></td>
              <td>
                <span class="badge" :class="sede.conectada ? 'ok' : 'off'">
                  {{ sede.conectada ? 'Conectada' : 'Desconectada' }}
                </span>
              </td>
              <td class="row-actions">
                <button
                  type="button"
                  class="icon-btn"
                  title="Editar"
                  aria-label="Editar sede"
                  @click="openEdit(sede)"
                >
                  <Pencil :size="16" :stroke-width="1.75" />
                </button>
                <button
                  type="button"
                  class="icon-btn danger"
                  title="Eliminar"
                  aria-label="Eliminar sede"
                  @click="askDelete(sede)"
                >
                  <Trash2 :size="16" :stroke-width="1.75" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ConfirmDialog
      v-model:open="confirmOpen"
      title="Eliminar sede"
      :message="confirmMessage"
      confirm-label="Eliminar"
      cancel-label="Cancelar"
      loading-label="Eliminando…"
      variant="danger"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

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
          :aria-labelledby="'sede-modal-title'"
        >
          <div class="modal-header">
            <h2 id="sede-modal-title" class="modal-title">{{ modalTitle }}</h2>
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
              <span>Nombre</span>
              <input v-model="form.nombre" type="text" required maxlength="255" autofocus />
              <span v-if="fieldErrors.nombre" class="field-error">{{ fieldErrors.nombre }}</span>
            </label>

            <label class="field">
              <span>Código</span>
              <input
                v-model="form.codigo"
                type="text"
                required
                maxlength="100"
                placeholder="central"
              />
              <span v-if="fieldErrors.codigo" class="field-error">{{ fieldErrors.codigo }}</span>
            </label>

            <label class="check">
              <input v-model="form.conectada" type="checkbox" />
              <span>Conectada</span>
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
  border: 1px solid var(--swc-border);
}

.banner.success {
  color: var(--swc-success);
  background: var(--swc-success-bg);
  border-color: transparent;
}

.banner.error {
  color: var(--swc-danger);
  background: var(--swc-danger-bg);
  border-color: transparent;
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

.field input {
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

.field input:focus {
  border-color: var(--swc-info);
}

.check {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--swc-text);
  font-size: 0.92rem;
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
  cursor: wait;
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

code {
  font-size: 0.85rem;
  color: var(--swc-text-muted);
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 500;
}

.badge.ok {
  color: var(--swc-success);
  background: var(--swc-success-bg);
}

.badge.off {
  color: var(--swc-text-muted);
  background: var(--swc-bg-soft);
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
  max-width: 440px;
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
