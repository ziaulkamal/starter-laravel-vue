<template>
  <AppLayout title="Master Senif">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Master Senif' }]" />
    </template>

    <!-- Flash -->
    <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-3">
      <i class="ti ti-circle-check fs-5"></i>
      <span>{{ $page.props.flash.success }}</span>
      <button type="button" class="btn-close ms-auto" @click="dismissFlash" />
    </div>
    <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-3">
      <i class="ti ti-alert-circle fs-5"></i>
      <span>{{ $page.props.flash.error }}</span>
      <button type="button" class="btn-close ms-auto" @click="dismissFlash" />
    </div>

    <div class="row g-3">
      <!-- Tabel senif -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Daftar Senif (Asnaf)</h5>
            <span class="badge bg-secondary">{{ senif.length }} senif</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Nama Senif</th>
                  <th>Kode Asnaf</th>
                  <th>Keterangan</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!senif.length">
                  <td colspan="6" class="text-center text-muted py-5">Belum ada senif. Tambahkan di sebelah kanan.</td>
                </tr>
                <tr v-for="(item, i) in senif" :key="item.id">
                  <td class="text-muted small">{{ i + 1 }}</td>
                  <td class="fw-medium">{{ item.nama }}</td>
                  <td><span class="badge bg-light text-dark font-monospace">{{ item.kode_asnaf }}</span></td>
                  <td class="small text-muted">{{ item.keterangan || '-' }}</td>
                  <td>
                    <span class="badge" :class="item.is_active ? 'bg-success' : 'bg-secondary'">
                      {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                      <button class="btn btn-sm btn-outline-primary" @click="startEdit(item)">
                        <i class="ti ti-pencil"></i>
                      </button>
                      <button class="btn btn-sm"
                        :class="item.is_active ? 'btn-outline-secondary' : 'btn-outline-success'"
                        @click="toggle(item)">
                        <i :class="item.is_active ? 'ti ti-ban' : 'ti ti-check'"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(item)">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Form tambah/edit -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header fw-medium">
            {{ editingId ? 'Edit Senif' : 'Tambah Senif Baru' }}
          </div>
          <div class="card-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Nama Senif <span class="text-danger">*</span></label>
                <input v-model="form.nama" type="text" class="form-control"
                  :class="{ 'is-invalid': errors.nama }" placeholder="cth: Fakir" />
                <div v-if="errors.nama" class="invalid-feedback">{{ errors.nama }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Kode Asnaf <span class="text-danger">*</span></label>
                <input v-model="form.kode_asnaf" type="text" class="form-control font-monospace"
                  :class="{ 'is-invalid': errors.kode_asnaf }"
                  placeholder="cth: fakir" :readonly="!!editingId" />
                <div class="form-text">Huruf kecil, tanpa spasi. Tidak bisa diubah setelah disimpan.</div>
                <div v-if="errors.kode_asnaf" class="invalid-feedback">{{ errors.kode_asnaf }}</div>
              </div>

              <div class="mb-4">
                <label class="form-label">Keterangan</label>
                <textarea v-model="form.keterangan" class="form-control" rows="3"
                  placeholder="Deskripsi singkat tentang senif ini..."></textarea>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-1" />
                  {{ editingId ? 'Simpan Perubahan' : 'Tambah Senif' }}
                </button>
                <button v-if="editingId" type="button" class="btn btn-secondary" @click="cancelEdit">
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Info 8 asnaf -->
        <div class="card mt-3">
          <div class="card-header small fw-medium text-muted">8 Asnaf Standar Islam</div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush small">
              <div v-for="a in standardAsnaf" :key="a.kode"
                class="list-group-item d-flex justify-content-between align-items-center py-2">
                <div>
                  <span class="fw-medium">{{ a.nama }}</span>
                  <span class="text-muted ms-1 font-monospace" style="font-size:.75rem">({{ a.kode }})</span>
                </div>
                <span v-if="asnafExists(a.kode)" class="badge bg-success-subtle text-success">✓</span>
                <span v-else class="badge bg-light text-muted">belum</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm delete modal -->
    <div v-if="deleteTarget" class="modal fade show d-block" style="background:rgba(0,0,0,.4)">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <i class="ti ti-alert-triangle fs-1 text-warning mb-2 d-block"></i>
            <p class="mb-0">Hapus senif <strong>{{ deleteTarget.nama }}</strong>?</p>
            <small class="text-muted">Senif yang sudah digunakan dalam pengajuan tidak dapat dihapus.</small>
          </div>
          <div class="modal-footer justify-content-center gap-2">
            <button class="btn btn-sm btn-secondary" @click="deleteTarget = null">Batal</button>
            <button class="btn btn-sm btn-danger" @click="doDelete">Hapus</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { AppBreadcrumb } from '@/Components/UI'

const props = defineProps<{
  senif: Array<{
    id: number
    nama: string
    kode_asnaf: string
    keterangan: string | null
    is_active: boolean
  }>
}>()

const standardAsnaf = [
  { kode: 'fakir',        nama: 'Fakir' },
  { kode: 'miskin',       nama: 'Miskin' },
  { kode: 'amil',         nama: 'Amil' },
  { kode: 'muallaf',      nama: 'Muallaf' },
  { kode: 'riqab',        nama: 'Riqab' },
  { kode: 'gharimin',     nama: 'Gharimin' },
  { kode: 'fisabilillah', nama: "Fi Sabilillah" },
  { kode: 'ibnu_sabil',   nama: 'Ibnu Sabil' },
]

const asnafExists = (kode: string) => props.senif.some(s => s.kode_asnaf === kode)

const editingId  = ref<number | null>(null)
const processing = ref(false)
const deleteTarget = ref<(typeof props.senif)[0] | null>(null)
const errors = ref<Record<string, string>>({})

const form = reactive({ nama: '', kode_asnaf: '', keterangan: '' })

function resetForm() {
  form.nama = ''
  form.kode_asnaf = ''
  form.keterangan = ''
  errors.value = {}
}

function startEdit(item: (typeof props.senif)[0]) {
  editingId.value = item.id
  form.nama = item.nama
  form.kode_asnaf = item.kode_asnaf
  form.keterangan = item.keterangan ?? ''
  errors.value = {}
}

function cancelEdit() {
  editingId.value = null
  resetForm()
}

function submitForm() {
  processing.value = true
  errors.value = {}

  const url = editingId.value
    ? route('admin.senif.update', editingId.value)
    : route('admin.senif.store')

  router[editingId.value ? 'put' : 'post'](url, { ...form }, {
    onSuccess: () => { cancelEdit() },
    onError: (e) => { errors.value = e },
    onFinish: () => { processing.value = false },
  })
}

function toggle(item: (typeof props.senif)[0]) {
  router.patch(route('admin.senif.toggle', item.id), {}, { preserveScroll: true })
}

function confirmDelete(item: (typeof props.senif)[0]) {
  deleteTarget.value = item
}

function doDelete() {
  if (!deleteTarget.value) return
  router.delete(route('admin.senif.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onFinish: () => { deleteTarget.value = null },
  })
}

const dismissFlash = () => {}
</script>
