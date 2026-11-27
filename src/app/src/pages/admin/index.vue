<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { Pencil, Plus, Trash2, X } from '@lucide/vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useFlashMessage } from '@/composables/useFlashMessage'
import { useAdminStore } from '@/stores/useAdminStore'

const adminStore = useAdminStore()
const { companies, users } = storeToRefs(adminStore)
const { message: success, flash: flashSuccess, clear: clearSuccess } = useFlashMessage()

const tab = ref('companies')
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const listError = ref('')
const formError = ref('')
const fieldErrors = ref({})
const modalOpen = ref(false)
const confirmOpen = ref(false)
const editingUserId = ref(null)
const pendingDelete = ref(null)
const pendingDeleteType = ref(null)

const companyForm = reactive({
  nombre: '',
  codigo: '',
})

const userForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 0,
  company_id: '',
})

const modalTitle = computed(() => {
  if (tab.value === 'companies') return 'Nueva empresa'
  return editingUserId.value ? 'Editar usuario' : 'Nuevo usuario'
})

const submitLabel = computed(() => {
  if (saving.value) return 'Guardando…'
  if (tab.value === 'companies') return 'Crear empresa'
  return editingUserId.value ? 'Guardar cambios' : 'Crear usuario'
})

const confirmTitle = computed(() =>
  pendingDeleteType.value === 'company' ? 'Eliminar empresa' : 'Eliminar usuario',
)

const confirmMessage = computed(() => {
  if (pendingDeleteType.value === 'company') {
    return `¿Eliminar la empresa "${pendingDelete.value?.nombre ?? ''}"? Los usuarios y sedes asociados quedarán sin empresa.`
  }
  return `¿Eliminar el usuario "${pendingDelete.value?.name ?? ''}"?`
})

function roleLabel(role) {
  return Number(role) === 1 ? 'Admin' : 'Usuario'
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

function resetCompanyForm() {
  companyForm.nombre = ''
  companyForm.codigo = ''
  fieldErrors.value = {}
  formError.value = ''
}

function resetUserForm() {
  editingUserId.value = null
  userForm.name = ''
  userForm.email = ''
  userForm.password = ''
  userForm.password_confirmation = ''
  userForm.role = 0
  userForm.company_id = ''
  fieldErrors.value = {}
  formError.value = ''
}

function openCreate() {
  clearSuccess()
  if (tab.value === 'companies') {
    resetCompanyForm()
  } else {
    resetUserForm()
  }
  modalOpen.value = true
}

function openEditUser(user) {
  editingUserId.value = user.id
  userForm.name = user.name
  userForm.email = user.email
  userForm.password = ''
  userForm.password_confirmation = ''
  userForm.role = Number(user.role) || 0
  userForm.company_id = user.company_id ?? ''
  fieldErrors.value = {}
  formError.value = ''
  clearSuccess()
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  resetCompanyForm()
  resetUserForm()
}

async function loadAll() {
  loading.value = true
  listError.value = ''
  try {
    await Promise.all([adminStore.fetchCompanies(), adminStore.fetchUsers()])
  } catch {
    listError.value = 'No se pudieron cargar los datos de administración.'
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
    if (tab.value === 'companies') {
      const { data } = await adminStore.storeCompany({
        nombre: companyForm.nombre.trim(),
        codigo: companyForm.codigo.trim(),
      })
      flashSuccess(data.message)
    } else {
      const payload = {
        name: userForm.name.trim(),
        email: userForm.email.trim(),
        role: Number(userForm.role),
        company_id: userForm.company_id === '' ? null : Number(userForm.company_id),
      }

      if (userForm.password || userForm.password_confirmation || !editingUserId.value) {
        payload.password = userForm.password
        payload.password_confirmation = userForm.password_confirmation
      }

      const { data } = editingUserId.value
        ? await adminStore.updateUser({ id: editingUserId.value, ...payload })
        : await adminStore.storeUser(payload)

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

function askDeleteCompany(company) {
  pendingDelete.value = company
  pendingDeleteType.value = 'company'
  confirmOpen.value = true
}

function askDeleteUser(user) {
  pendingDelete.value = user
  pendingDeleteType.value = 'user'
  confirmOpen.value = true
}

function cancelDelete() {
  confirmOpen.value = false
  pendingDelete.value = null
  pendingDeleteType.value = null
}

async function confirmDelete() {
  const item = pendingDelete.value
  const type = pendingDeleteType.value
  if (!item || !type) return

  deleting.value = true
  listError.value = ''
  clearSuccess()
  try {
    const { data } =
      type === 'company'
        ? await adminStore.deleteCompany(item)
        : await adminStore.deleteUser(item)

    if (type === 'user' && editingUserId.value === item.id) closeModal()
    flashSuccess(data.message)
    confirmOpen.value = false
    pendingDelete.value = null
    pendingDeleteType.value = null
    await loadAll()
  } catch (err) {
    listError.value = extractError(err)
    confirmOpen.value = false
    pendingDelete.value = null
    pendingDeleteType.value = null
  } finally {
    deleting.value = false
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && modalOpen.value && !confirmOpen.value) closeModal()
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
        <h1 class="page-title">Administración</h1>
        <p class="page-sub">Gestiona empresas y usuarios de la plataforma.</p>
      </div>
      <button type="button" class="btn primary" @click="openCreate">
        <Plus :size="16" :stroke-width="2" aria-hidden="true" />
        {{ tab === 'companies' ? 'Crear empresa' : 'Crear usuario' }}
      </button>
    </header>

    <div class="tabs" role="tablist" aria-label="Secciones de administración">
      <button
        type="button"
        role="tab"
        class="tab"
        :class="{ active: tab === 'companies' }"
        :aria-selected="tab === 'companies'"
        @click="tab = 'companies'"
      >
        Empresas
      </button>
      <button
        type="button"
        role="tab"
        class="tab"
        :class="{ active: tab === 'users' }"
        :aria-selected="tab === 'users'"
        @click="tab = 'users'"
      >
        Usuarios
      </button>
    </div>

    <p v-if="success" class="banner success" role="status">{{ success }}</p>
    <p v-if="listError" class="banner error" role="alert">{{ listError }}</p>

    <section v-if="tab === 'companies'" class="panel">
      <p v-if="loading" class="status">Cargando empresas…</p>
      <div v-else-if="companies.length === 0" class="empty">
        <p class="empty-title">Aún no hay empresas</p>
        <p class="empty-sub">Crea la primera empresa con el botón de arriba.</p>
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Código</th>
              <th>Usuarios</th>
              <th>Sedes</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="company in companies" :key="company.id">
              <td>{{ company.nombre }}</td>
              <td><code>{{ company.codigo }}</code></td>
              <td>{{ company.users_count }}</td>
              <td>{{ company.sedes_count }}</td>
              <td class="row-actions">
                <button
                  type="button"
                  class="icon-btn danger"
                  title="Eliminar"
                  aria-label="Eliminar empresa"
                  @click="askDeleteCompany(company)"
                >
                  <Trash2 :size="16" :stroke-width="1.75" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section v-else class="panel">
      <p v-if="loading" class="status">Cargando usuarios…</p>
      <div v-else-if="users.length === 0" class="empty">
        <p class="empty-title">Aún no hay usuarios</p>
        <p class="empty-sub">Crea el primer usuario con el botón de arriba.</p>
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Empresa</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>
                <span class="badge" :class="Number(user.role) === 1 ? 'admin' : 'user'">
                  {{ roleLabel(user.role) }}
                </span>
              </td>
              <td>{{ user.company?.nombre || '—' }}</td>
              <td class="row-actions">
                <button
                  type="button"
                  class="icon-btn"
                  title="Editar"
                  aria-label="Editar usuario"
                  @click="openEditUser(user)"
                >
                  <Pencil :size="16" :stroke-width="1.75" />
                </button>
                <button
                  type="button"
                  class="icon-btn danger"
                  title="Eliminar"
                  aria-label="Eliminar usuario"
                  @click="askDeleteUser(user)"
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
      :title="confirmTitle"
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
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="admin-modal-title">
          <div class="modal-header">
            <h2 id="admin-modal-title" class="modal-title">{{ modalTitle }}</h2>
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
            <template v-if="tab === 'companies'">
              <label class="field">
                <span>Nombre</span>
                <input v-model="companyForm.nombre" type="text" required maxlength="255" autofocus />
                <span v-if="fieldErrors.nombre" class="field-error">{{ fieldErrors.nombre }}</span>
              </label>

              <label class="field">
                <span>Código</span>
                <input
                  v-model="companyForm.codigo"
                  type="text"
                  required
                  maxlength="100"
                  placeholder="acme"
                />
                <span v-if="fieldErrors.codigo" class="field-error">{{ fieldErrors.codigo }}</span>
              </label>
            </template>

            <template v-else>
              <label class="field">
                <span>Nombre</span>
                <input v-model="userForm.name" type="text" required maxlength="255" autofocus />
                <span v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</span>
              </label>

              <label class="field">
                <span>Email</span>
                <input v-model="userForm.email" type="email" required maxlength="255" />
                <span v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email }}</span>
              </label>

              <label class="field">
                <span>{{ editingUserId ? 'Nueva contraseña' : 'Contraseña' }}</span>
                <input
                  v-model="userForm.password"
                  type="password"
                  :required="!editingUserId"
                  autocomplete="new-password"
                />
                <span v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</span>
              </label>

              <label class="field">
                <span>Confirmar contraseña</span>
                <input
                  v-model="userForm.password_confirmation"
                  type="password"
                  :required="!editingUserId"
                  autocomplete="new-password"
                />
              </label>

              <label class="field">
                <span>Rol</span>
                <select v-model.number="userForm.role" required>
                  <option :value="0">Usuario</option>
                  <option :value="1">Admin</option>
                </select>
                <span v-if="fieldErrors.role" class="field-error">{{ fieldErrors.role }}</span>
              </label>

              <label class="field">
                <span>Empresa</span>
                <select v-model="userForm.company_id">
                  <option value="">Sin empresa</option>
                  <option v-for="company in companies" :key="company.id" :value="company.id">
                    {{ company.nombre }}
                  </option>
                </select>
                <span v-if="fieldErrors.company_id" class="field-error">{{ fieldErrors.company_id }}</span>
              </label>
            </template>

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

.tabs {
  display: flex;
  gap: 0.35rem;
}

.tab {
  appearance: none;
  border: 1px solid transparent;
  background: transparent;
  color: var(--swc-text-muted);
  font: inherit;
  font-size: 0.92rem;
  padding: 0.45rem 0.8rem;
  border-radius: var(--swc-radius-sm);
  cursor: pointer;
}

.tab:hover {
  color: var(--swc-text);
}

.tab.active {
  color: var(--swc-heading);
  background: var(--swc-bg-elevated);
  border-color: var(--swc-border);
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

.badge.admin {
  color: var(--swc-info);
  background: color-mix(in srgb, var(--swc-info) 16%, transparent);
}

.badge.user {
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
