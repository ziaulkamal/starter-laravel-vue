<template>
  <div class="modal fade show d-block" style="background:rgba(0,0,0,.45)" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Upload Berkas Dokumen</h6>
          <button type="button" class="btn-close" @click="$emit('close')" />
        </div>

        <div class="modal-body">
          <!-- Jenis berkas -->
          <div class="mb-3">
            <label class="form-label">Jenis Berkas <span class="text-danger">*</span></label>
            <select v-model="jenis_berkas" class="form-select" :class="{ 'is-invalid': errors.jenis }">
              <option value="">— Pilih jenis berkas —</option>
              <option value="ktp">KTP</option>
              <option value="kk">Kartu Keluarga</option>
              <option value="surat_keterangan">Surat Keterangan</option>
              <option value="foto_rumah">Foto Rumah</option>
              <option value="lainnya">Lainnya</option>
            </select>
            <div v-if="errors.jenis" class="invalid-feedback">{{ errors.jenis }}</div>
          </div>

          <!-- File input -->
          <div class="mb-3">
            <label class="form-label">File <span class="text-danger">*</span></label>
            <input ref="fileInput" type="file" class="form-control" :class="{ 'is-invalid': errors.files }"
              accept=".pdf,.jpg,.jpeg,.png" multiple @change="onFileChange" />
            <div class="form-text">Maks. 5 file, masing-masing maks. 5 MB. Format: PDF, JPG, PNG.</div>
            <div v-if="errors.files" class="invalid-feedback">{{ errors.files }}</div>
          </div>

          <!-- Preview -->
          <div v-if="previews.length" class="d-flex flex-wrap gap-2 mb-3">
            <div v-for="(p, i) in previews" :key="i"
              class="border rounded p-2 d-flex align-items-center gap-2 small" style="max-width:200px">
              <img v-if="p.isImage" :src="p.url" class="rounded" style="width:40px;height:40px;object-fit:cover" />
              <i v-else class="ti ti-file-type-pdf text-danger fs-4"></i>
              <div class="flex-grow-1 text-truncate">
                <div class="text-truncate" style="max-width:110px">{{ p.name }}</div>
                <div class="text-muted">{{ p.size }}</div>
              </div>
              <button type="button" class="btn-close btn-close-sm" @click="removeFile(i)" />
            </div>
          </div>

          <!-- Progress -->
          <div v-if="uploading" class="mb-2">
            <div class="d-flex justify-content-between small mb-1">
              <span>Mengunggah...</span><span>{{ progress }}%</span>
            </div>
            <div class="progress" style="height:6px">
              <div class="progress-bar" :style="{ width: progress + '%' }" role="progressbar" />
            </div>
          </div>

          <!-- Server errors -->
          <div v-if="serverError" class="alert alert-danger small py-2">{{ serverError }}</div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary btn-sm" :disabled="uploading" @click="$emit('close')">Batal</button>
          <button class="btn btn-primary btn-sm" :disabled="uploading || !canSubmit" @click="doUpload">
            <span v-if="uploading" class="spinner-border spinner-border-sm me-1" />
            {{ uploading ? 'Mengunggah...' : 'Upload' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps<{ mustahikId: number }>()
const emit = defineEmits<{ (e: 'uploaded'): void; (e: 'close'): void }>()

const jenis_berkas = ref('')
const files = ref<File[]>([])
const previews = ref<{ name: string; size: string; isImage: boolean; url: string }[]>([])
const uploading = ref(false)
const progress = ref(0)
const errors = ref<{ jenis?: string; files?: string }>({})
const serverError = ref('')
const fileInput = ref<HTMLInputElement>()

const canSubmit = computed(() => jenis_berkas.value && files.value.length > 0)

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const selected = Array.from(input.files ?? [])

  errors.value = {}
  serverError.value = ''

  if (files.value.length + selected.length > 5) {
    errors.value.files = 'Maksimal 5 file.'
    if (fileInput.value) fileInput.value.value = ''
    return
  }

  const oversized = selected.filter(f => f.size > 5 * 1024 * 1024)
  if (oversized.length) {
    errors.value.files = `File melebihi 5 MB: ${oversized.map(f => f.name).join(', ')}`
    if (fileInput.value) fileInput.value.value = ''
    return
  }

  for (const f of selected) {
    files.value.push(f)
    const isImage = f.type.startsWith('image/')
    previews.value.push({
      name: f.name,
      size: formatSize(f.size),
      isImage,
      url: isImage ? URL.createObjectURL(f) : '',
    })
  }

  if (fileInput.value) fileInput.value.value = ''
}

function removeFile(i: number) {
  if (previews.value[i].isImage) URL.revokeObjectURL(previews.value[i].url)
  files.value.splice(i, 1)
  previews.value.splice(i, 1)
}

async function doUpload() {
  errors.value = {}
  serverError.value = ''

  if (!jenis_berkas.value) { errors.value.jenis = 'Jenis berkas wajib dipilih.'; return }
  if (!files.value.length) { errors.value.files = 'Pilih minimal 1 file.'; return }

  const fd = new FormData()
  fd.append('mustahik_id', String(props.mustahikId))
  fd.append('jenis_berkas', jenis_berkas.value)
  files.value.forEach(f => fd.append('files[]', f))

  uploading.value = true
  progress.value = 0

  try {
    await axios.post(route('berkas.store'), fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress(e) {
        progress.value = Math.round((e.loaded / (e.total ?? 1)) * 100)
      },
    })
    emit('uploaded')
  } catch (err: any) {
    const data = err.response?.data
    if (data?.errors) {
      errors.value.files = Object.values(data.errors).flat().join(' ')
    } else {
      serverError.value = data?.message ?? 'Terjadi kesalahan saat mengunggah.'
    }
  } finally {
    uploading.value = false
  }
}
</script>
