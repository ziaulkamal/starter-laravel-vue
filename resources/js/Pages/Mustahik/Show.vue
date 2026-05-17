<template>
  <AppLayout :title="mustahik.nama_lengkap">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Mustahik', href: backUrl }, { label: mustahik.nama_lengkap }]" />
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

    <!-- Header -->
    <div class="card mb-3">
      <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h5 class="mb-1">{{ mustahik.nama_lengkap }}</h5>
          <div class="text-muted small">
            {{ mustahik.tempat_lahir }}, {{ formatDate(mustahik.tanggal_lahir) }}
            &nbsp;&bull;&nbsp;
            {{ mustahik.nik || 'NIK belum diisi' }}
          </div>
          <span class="badge mt-1" :class="mustahik.status === 'aktif' ? 'bg-success' : 'bg-danger'">
            {{ mustahik.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button v-if="canAjukan" class="btn btn-sm btn-primary" @click="showAjukanModal = true">
            <i class="ti ti-file-plus me-1"></i>Ajukan Bantuan {{ tahunSekarang }}
          </button>
          <Link v-if="canEdit" :href="editUrl" class="btn btn-sm btn-outline-primary">
            <i class="ti ti-pencil me-1"></i>Edit
          </Link>
          <button v-if="canToggle && mustahik.status === 'aktif'"
            class="btn btn-sm btn-outline-danger" @click="confirmToggle('nonaktifkan')">
            <i class="ti ti-ban me-1"></i>Nonaktifkan
          </button>
          <button v-if="canToggle && mustahik.status === 'nonaktif'"
            class="btn btn-sm btn-outline-success" @click="confirmToggle('aktifkan')">
            <i class="ti ti-check me-1"></i>Aktifkan
          </button>
        </div>
      </div>
    </div>

    <!-- Status pengajuan tahun ini (gampong view) -->
    <div v-if="pengajuanTahunIni" class="card mb-3 border-start border-4"
      :class="statusBorderClass(pengajuanTahunIni.status)">
      <div class="card-body py-3">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
          <div>
            <div class="small text-muted mb-1">Pengajuan Bantuan {{ tahunSekarang }}</div>
            <div class="fw-semibold">{{ pengajuanTahunIni.substansi_kategori?.nama }}</div>
            <div v-if="pengajuanTahunIni.alasan_penolakan" class="text-danger small mt-1">
              <i class="ti ti-alert-circle me-1"></i>Alasan penolakan: {{ pengajuanTahunIni.alasan_penolakan }}
            </div>
            <div v-if="pengajuanTahunIni.catatan_pengaju" class="text-muted small mt-1">
              Catatan pengaju: {{ pengajuanTahunIni.catatan_pengaju }}
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge fs-6 px-3 py-2" :class="statusBadgeClass(pengajuanTahunIni.status)">
              {{ statusLabel(pengajuanTahunIni.status) }}
            </span>
            <button v-if="pengajuanTahunIni.status === 'ditolak'" class="btn btn-sm btn-outline-warning"
              @click="showSanggahModal = true">
              <i class="ti ti-message-circle me-1"></i>Sanggah
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <button class="nav-link" :class="{ active: tab === 'data' }" @click="tab = 'data'">
          <i class="ti ti-user me-1"></i>Data Pribadi
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{ active: tab === 'berkas' }" @click="tab = 'berkas'">
          <i class="ti ti-paperclip me-1"></i>Berkas
          <span class="badge bg-secondary ms-1">{{ mustahik.berkas_files?.length ?? 0 }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{ active: tab === 'pengajuan' }" @click="tab = 'pengajuan'">
          <i class="ti ti-file-text me-1"></i>Riwayat Pengajuan
          <span class="badge bg-secondary ms-1">{{ mustahik.pengajuan_bantuan?.length ?? 0 }}</span>
        </button>
      </li>
    </ul>

    <!-- Tab: Data Pribadi -->
    <div v-show="tab === 'data'" class="card">
      <div class="card-body">
        <div class="row g-4">

          <div class="col-12">
            <p class="text-muted text-uppercase small fw-bold mb-3">Identitas</p>
            <div class="row g-3">
              <div class="col-md-6"><InfoField label="Nama Lengkap" :value="mustahik.nama_lengkap" /></div>
              <div class="col-md-3"><InfoField label="NIK" :value="mustahik.nik || '-'" mono /></div>
              <div class="col-md-3"><InfoField label="Jenis Kelamin" :value="labelJK(mustahik.jenis_kelamin)" /></div>
              <div class="col-md-3"><InfoField label="Tempat Lahir" :value="mustahik.tempat_lahir" /></div>
              <div class="col-md-3"><InfoField label="Tanggal Lahir" :value="formatDate(mustahik.tanggal_lahir)" /></div>
              <div class="col-md-3"><InfoField label="Status Pernikahan" :value="labelStatusNikah(mustahik.status_pernikahan)" /></div>
              <div class="col-md-3"><InfoField label="Pekerjaan" :value="mustahik.pekerjaan" /></div>
            </div>
          </div>

          <div class="col-12"><hr class="my-0" /></div>

          <div class="col-12">
            <p class="text-muted text-uppercase small fw-bold mb-3">Wilayah</p>
            <div class="row g-3">
              <div class="col-md-3"><InfoField label="Provinsi" :value="mustahik.provinsi?.nama" /></div>
              <div class="col-md-3"><InfoField label="Kabupaten" :value="mustahik.kabupaten?.nama" /></div>
              <div class="col-md-3"><InfoField label="Kecamatan" :value="mustahik.kecamatan?.nama" /></div>
              <div class="col-md-3"><InfoField label="Desa/Gampong" :value="mustahik.desa?.nama" /></div>
              <div class="col-md-6"><InfoField label="Alamat Gampong" :value="mustahik.alamat_gampong" /></div>
              <div class="col-md-6"><InfoField label="Dusun" :value="mustahik.alamat_dusun || '-'" /></div>
            </div>
          </div>

          <div class="col-12"><hr class="my-0" /></div>

          <div class="col-12">
            <p class="text-muted text-uppercase small fw-bold mb-3">Kontak & Rekening</p>
            <div class="row g-3">
              <div class="col-md-4"><InfoField label="No. HP" :value="mustahik.no_hp || '-'" /></div>
              <div class="col-md-4"><InfoField label="Pemilik Rekening" :value="mustahik.pemilik_rekening || '-'" /></div>
              <div class="col-md-4"><InfoField label="Nomor Rekening" :value="mustahik.nomor_rekening || '-'" mono /></div>
            </div>
          </div>

          <template v-if="mustahik.jumlah_anggota || mustahik.range_penghasilan">
            <div class="col-12"><hr class="my-0" /></div>
            <div class="col-12">
              <p class="text-muted text-uppercase small fw-bold mb-3">Kondisi Keluarga & Ekonomi</p>
              <div class="row g-3">
                <div class="col-md-3"><InfoField label="Jumlah Anggota" :value="labelAnggota(mustahik.jumlah_anggota)" /></div>
                <div class="col-md-3"><InfoField label="Jumlah Tanggungan" :value="labelTanggungan(mustahik.jumlah_tanggungan)" /></div>
                <div class="col-md-3"><InfoField label="Penghasilan" :value="labelPenghasilan(mustahik.range_penghasilan)" /></div>
                <div class="col-md-3"><InfoField label="Pencari Nafkah" :value="labelNafkah(mustahik.status_pencari_nafkah)" /></div>
              </div>
            </div>
          </template>

          <template v-if="mustahik.catatan">
            <div class="col-12"><hr class="my-0" /></div>
            <div class="col-12">
              <p class="text-muted text-uppercase small fw-bold mb-2">Catatan</p>
              <p class="mb-0">{{ mustahik.catatan }}</p>
            </div>
          </template>

        </div>
      </div>
      <div class="card-footer text-muted small">
        Didaftarkan oleh <strong>{{ mustahik.created_by?.name ?? 'Sistem' }}</strong>
        &nbsp;&bull;&nbsp;{{ formatDateTime(mustahik.created_at) }}
      </div>
    </div>

    <!-- Tab: Berkas -->
    <div v-show="tab === 'berkas'">
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span class="fw-medium">Berkas Dokumen</span>
          <button v-if="canEdit" class="btn btn-sm btn-primary" @click="showUpload = true">
            <i class="ti ti-upload me-1"></i>Upload Berkas
          </button>
        </div>
        <div v-if="!mustahik.berkas_files?.length" class="card-body text-center text-muted py-5">
          Belum ada berkas yang diunggah.
        </div>
        <div v-else class="list-group list-group-flush">
          <div v-for="berkas in mustahik.berkas_files" :key="berkas.id"
            class="list-group-item d-flex align-items-center gap-3 py-2">
            <i class="fs-4"
              :class="berkas.mime_type?.startsWith('image/') ? 'ti ti-photo text-info' : 'ti ti-file-type-pdf text-danger'"></i>
            <div class="flex-grow-1">
              <div class="fw-medium small">{{ labelJenisBerkas(berkas.jenis_berkas) }}</div>
              <div class="text-muted" style="font-size:.75rem">
                {{ berkas.nama_file }} &bull; {{ berkas.ukuran_format }}
              </div>
            </div>
            <div class="d-flex gap-1">
              <a :href="downloadBerkasUrl(berkas.id)" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-download"></i>
              </a>
              <button v-if="canEdit" class="btn btn-sm btn-outline-danger" @click="deleteBerkas(berkas.id)">
                <i class="ti ti-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <UploadBerkas v-if="showUpload" :mustahik-id="mustahik.id"
        @uploaded="onUploaded" @close="showUpload = false" />
    </div>

    <!-- Tab: Riwayat Pengajuan -->
    <div v-show="tab === 'pengajuan'" class="card">
      <div class="card-header fw-medium">Riwayat Pengajuan Bantuan</div>
      <div v-if="!mustahik.pengajuan_bantuan?.length" class="card-body text-center text-muted py-5">
        Belum ada riwayat pengajuan.
      </div>
      <div v-else class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Tahun</th>
              <th>Senif</th>
              <th>Tanggal Pengajuan</th>
              <th>Status</th>
              <th>Alasan / Catatan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in mustahik.pengajuan_bantuan" :key="p.id">
              <td class="fw-medium">{{ p.tahun }}</td>
              <td class="small">{{ p.substansi_kategori?.nama ?? '-' }}</td>
              <td class="small">{{ formatDate(p.tanggal_pengajuan) }}</td>
              <td>
                <span class="badge" :class="statusBadgeClass(p.status)">{{ statusLabel(p.status) }}</span>
              </td>
              <td class="small text-muted">
                <span v-if="p.alasan_penolakan" class="text-danger">{{ p.alasan_penolakan }}</span>
                <span v-else-if="p.catatan_pengaju">{{ p.catatan_pengaju }}</span>
                <span v-else>-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Ajukan Bantuan -->
    <div v-if="showAjukanModal" class="modal fade show d-block" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajukan Bantuan {{ tahunSekarang }}</h5>
            <button type="button" class="btn-close" @click="closeAjukanModal" />
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-medium">Kategori Senif / Asnaf <span class="text-danger">*</span></label>
              <select v-model="ajukanForm.substansi_kategori_id" class="form-select"
                :class="{ 'is-invalid': ajukanErrors.substansi_kategori_id }">
                <option value="">— Pilih Senif —</option>
                <option v-for="s in senif" :key="s.id" :value="s.id">
                  {{ s.nama }} ({{ s.kode_asnaf }})
                </option>
              </select>
              <div v-if="ajukanErrors.substansi_kategori_id" class="invalid-feedback">
                {{ ajukanErrors.substansi_kategori_id }}
              </div>
            </div>
            <div class="mb-1">
              <label class="form-label fw-medium">Catatan (opsional)</label>
              <textarea v-model="ajukanForm.catatan_pengaju" class="form-control" rows="3"
                placeholder="Keterangan tambahan dari pengaju..." />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeAjukanModal">Batal</button>
            <button class="btn btn-primary" :disabled="ajukanProcessing" @click="submitAjukan">
              <span v-if="ajukanProcessing" class="spinner-border spinner-border-sm me-1" />
              Ajukan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Sanggah -->
    <div v-if="showSanggahModal" class="modal fade show d-block" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Sanggah Penolakan</h5>
            <button type="button" class="btn-close" @click="showSanggahModal = false" />
          </div>
          <div class="modal-body">
            <div class="alert alert-warning d-flex gap-2 align-items-start">
              <i class="ti ti-alert-triangle mt-1"></i>
              <div>
                Pengajuan anda ditolak dengan alasan:
                <strong>{{ pengajuanTahunIni?.alasan_penolakan || '-' }}</strong>
              </div>
            </div>
            <div>
              <label class="form-label fw-medium">Catatan Sanggahan (opsional)</label>
              <textarea v-model="sanggahForm.catatan_pengaju" class="form-control" rows="3"
                placeholder="Jelaskan alasan sanggahan anda..." />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showSanggahModal = false">Batal</button>
            <button class="btn btn-warning" :disabled="sanggahProcessing" @click="submitSanggah">
              <span v-if="sanggahProcessing" class="spinner-border spinner-border-sm me-1" />
              Kirim Sanggahan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm toggle modal -->
    <div v-if="toggleModal" class="modal fade show d-block" style="background:rgba(0,0,0,.4)">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <i class="ti ti-alert-triangle fs-1 text-warning mb-2 d-block"></i>
            <p class="mb-1">
              {{ toggleAction === 'nonaktifkan' ? 'Nonaktifkan' : 'Aktifkan' }} mustahik
              <strong>{{ mustahik.nama_lengkap }}</strong>?
            </p>
          </div>
          <div class="modal-footer justify-content-center gap-2">
            <button class="btn btn-sm btn-secondary" @click="toggleModal = false">Batal</button>
            <button class="btn btn-sm"
              :class="toggleAction === 'nonaktifkan' ? 'btn-danger' : 'btn-success'"
              @click="doToggle">Ya, Lanjutkan</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { AppBreadcrumb } from '@/Components/UI'
import UploadBerkas from '@/Components/Mustahik/UploadBerkas.vue'

const InfoField = {
  props: { label: String, value: String, mono: Boolean },
  template: `
    <div>
      <div class="text-muted small mb-1">{{ label }}</div>
      <div :class="['fw-medium', mono ? 'font-monospace' : '']">{{ value || '-' }}</div>
    </div>
  `,
}

const props = defineProps<{
  mustahik: any
  backUrl: string
  editUrl: string
  canEdit: boolean
  canToggle: boolean
  canAjukan: boolean
  pengajuanTahunIni: any | null
  senif: Array<{ id: number; nama: string; kode_asnaf: string }>
  tahunSekarang: number
}>()

const tab          = ref<'data' | 'berkas' | 'pengajuan'>('data')
const showUpload   = ref(false)
const toggleModal  = ref(false)
const toggleAction = ref<'nonaktifkan' | 'aktifkan'>('nonaktifkan')

// Ajukan bantuan
const showAjukanModal  = ref(false)
const ajukanProcessing = ref(false)
const ajukanErrors     = ref<Record<string, string>>({})
const ajukanForm       = reactive({ substansi_kategori_id: '' as string | number, catatan_pengaju: '' })

function closeAjukanModal() {
  showAjukanModal.value = false
  ajukanForm.substansi_kategori_id = ''
  ajukanForm.catatan_pengaju = ''
  ajukanErrors.value = {}
}

function submitAjukan() {
  ajukanProcessing.value = true
  ajukanErrors.value = {}
  router.post(
    route('gampong.pengajuan.store', props.mustahik.id),
    { ...ajukanForm },
    {
      onSuccess: () => closeAjukanModal(),
      onError:   (e) => { ajukanErrors.value = e },
      onFinish:  () => { ajukanProcessing.value = false },
    },
  )
}

// Sanggah
const showSanggahModal  = ref(false)
const sanggahProcessing = ref(false)
const sanggahForm       = reactive({ catatan_pengaju: '' })

function submitSanggah() {
  if (!props.pengajuanTahunIni) return
  sanggahProcessing.value = true
  router.post(
    route('gampong.pengajuan.sanggah', props.pengajuanTahunIni.id),
    { ...sanggahForm },
    {
      onSuccess: () => { showSanggahModal.value = false },
      onFinish:  () => { sanggahProcessing.value = false },
    },
  )
}

// Toggle aktif/nonaktif
function confirmToggle(action: 'nonaktifkan' | 'aktifkan') {
  toggleAction.value = action
  toggleModal.value  = true
}

function doToggle() {
  const isAdmin    = props.backUrl.includes('/admin/')
  const routeName  = isAdmin
    ? `admin.mustahik.${toggleAction.value}`
    : `gampong.mustahik.${toggleAction.value}`
  router.patch(route(routeName, props.mustahik.id), {}, {
    onSuccess: () => { toggleModal.value = false },
  })
}

function deleteBerkas(berkasId: number) {
  if (!confirm('Hapus berkas ini?')) return
  router.delete(route('berkas.destroy', berkasId), { preserveScroll: true })
}

const downloadBerkasUrl = (id: number) => route('berkas.download', id)

function onUploaded() {
  showUpload.value = false
  router.reload({ only: ['mustahik'] })
}

const dismissFlash = () => {}

const formatDate     = (d: string) => d ? new Date(d).toLocaleDateString('id-ID') : '-'
const formatDateTime = (d: string) => d ? new Date(d).toLocaleString('id-ID') : '-'

const labelJK          = (v: string) => ({ 'laki-laki': 'Laki-laki', perempuan: 'Perempuan' }[v] ?? v ?? '-')
const labelStatusNikah = (v: string) => ({ kawin: 'Kawin', belum_kawin: 'Belum Kawin', janda: 'Janda', duda: 'Duda' }[v] ?? v ?? '-')
const labelAnggota     = (v: string) => ({ tidak_ada: 'Tidak ada', '1-2': '1–2 orang', '2-5': '2–5 orang', lebih_5: 'Lebih dari 5' }[v] ?? v ?? '-')
const labelTanggungan  = labelAnggota
const labelPenghasilan = (v: string) => ({
  tidak_ada: 'Tidak ada', kurang_1500000: '< Rp 1.500.000',
  '1500000_2500000': 'Rp 1.500.000 – Rp 2.500.000', lebih_2500000: '> Rp 2.500.000',
}[v] ?? v ?? '-')
const labelNafkah      = (v: string) => ({ utama: 'Pencari nafkah utama', sampingan: 'Sampingan', tidak_mencari: 'Tidak mencari nafkah' }[v] ?? v ?? '-')
const labelJenisBerkas = (v: string) => ({
  ktp: 'KTP', kk: 'Kartu Keluarga', surat_keterangan: 'Surat Keterangan',
  foto_rumah: 'Foto Rumah', lainnya: 'Lainnya',
}[v] ?? v)

const statusLabel = (v: string) => ({
  diajukan: 'Diajukan', ditolak: 'Ditolak', sanggah: 'Disanggah',
  disetujui: 'Disetujui', disurvey: 'Dalam Survei', selesai: 'Selesai',
}[v] ?? v)

const statusBadgeClass = (v: string) => ({
  diajukan: 'bg-warning text-dark',
  ditolak:  'bg-danger',
  sanggah:  'bg-warning text-dark',
  disetujui:'bg-success',
  disurvey: 'bg-info text-dark',
  selesai:  'bg-primary',
}[v] ?? 'bg-secondary')

const statusBorderClass = (v: string) => ({
  diajukan: 'border-warning',
  ditolak:  'border-danger',
  sanggah:  'border-warning',
  disetujui:'border-success',
  disurvey: 'border-info',
  selesai:  'border-primary',
}[v] ?? 'border-secondary')
</script>
