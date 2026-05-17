<template>
  <form @submit.prevent="$emit('submit')">

    <!-- BAGIAN A: Identitas -->
    <div class="card mb-4">
      <div class="card-header fw-semibold">A. Identitas Mustahik / Miskin</div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-md-6">
            <AppInput v-model="form.nama_lengkap" label="Nama Lengkap" required :error="errors.nama_lengkap" />
          </div>
          <div class="col-md-6">
            <AppInput v-model="form.nik" label="NIK" maxlength="16" placeholder="16 digit" :error="errors.nik" />
          </div>
          <div class="col-md-4">
            <AppInput v-model="form.tempat_lahir" label="Tempat Lahir" required :error="errors.tempat_lahir" />
          </div>
          <div class="col-md-4">
            <AppInput v-model="form.tanggal_lahir" type="date" label="Tanggal Lahir" required :error="errors.tanggal_lahir" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
            <div class="d-flex gap-4 mt-1">
              <AppRadio v-model="form.jenis_kelamin" value="laki-laki" label="Laki-laki" />
              <AppRadio v-model="form.jenis_kelamin" value="perempuan" label="Perempuan" />
            </div>
            <div v-if="errors.jenis_kelamin" class="text-danger small mt-1">{{ errors.jenis_kelamin }}</div>
          </div>

          <!-- Wilayah Cascade -->
          <div class="col-12">
            <WilayahCascade
              v-model="wilayah"
              :readonly="readonlyWilayah"
              :errors="{ kode_kecamatan: errors.kode_kecamatan, kode_desa: errors.kode_desa }"
            />
          </div>

          <div class="col-md-6">
            <AppInput v-model="form.alamat_gampong" label="Alamat Gampong" required :error="errors.alamat_gampong" />
          </div>
          <div class="col-md-6">
            <AppInput v-model="form.alamat_dusun" label="Alamat Dusun" :error="errors.alamat_dusun" />
          </div>
          <div class="col-md-4">
            <AppInput v-model="form.pekerjaan" label="Pekerjaan" required :error="errors.pekerjaan" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Status Pernikahan <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3 mt-1">
              <AppRadio v-for="opt in statusPernikahanOpts" :key="opt.value"
                v-model="form.status_pernikahan" :value="opt.value" :label="opt.label" />
            </div>
            <div v-if="errors.status_pernikahan" class="text-danger small mt-1">{{ errors.status_pernikahan }}</div>
          </div>
          <div class="col-md-4">
            <AppInput v-model="form.no_hp" label="No HP / HP Keluarga" :error="errors.no_hp" />
          </div>

          <!-- Rekening: hanya admin kabupaten -->
          <template v-if="mode === 'admin'">
            <div class="col-md-6">
              <AppInput v-model="form.pemilik_rekening" label="Nama Pemilik Rekening" :error="errors.pemilik_rekening" />
            </div>
            <div class="col-md-6">
              <AppInput v-model="form.nomor_rekening" label="Nomor Rekening" :error="errors.nomor_rekening" />
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- BAGIAN B & C: hanya admin kabupaten (diisi saat survei) -->
    <template v-if="mode === 'admin'">
      <!-- BAGIAN B: Kondisi Keluarga -->
      <div class="card mb-4">
        <div class="card-header fw-semibold">B. Kondisi Keluarga</div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-medium">Jumlah Anggota Keluarga <span class="text-danger">*</span></label>
              <div class="d-flex flex-column gap-2">
                <AppRadio v-for="opt in jumlahOpts" :key="opt.value"
                  v-model="form.jumlah_anggota" :value="opt.value" :label="opt.label" />
              </div>
              <div v-if="errors.jumlah_anggota" class="text-danger small mt-1">{{ errors.jumlah_anggota }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Jumlah Tanggungan dalam Keluarga <span class="text-danger">*</span></label>
              <div class="d-flex flex-column gap-2">
                <AppRadio v-for="opt in jumlahOpts" :key="opt.value"
                  v-model="form.jumlah_tanggungan" :value="opt.value" :label="opt.label" />
              </div>
              <div v-if="errors.jumlah_tanggungan" class="text-danger small mt-1">{{ errors.jumlah_tanggungan }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- BAGIAN C: Kondisi Ekonomi -->
      <div class="card mb-4">
        <div class="card-header fw-semibold">C. Kondisi Ekonomi Keluarga</div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-medium">Jumlah Penghasilan per Bulan <span class="text-danger">*</span></label>
              <div class="d-flex flex-column gap-2">
                <AppRadio v-for="opt in penghasilanOpts" :key="opt.value"
                  v-model="form.range_penghasilan" :value="opt.value" :label="opt.label" />
              </div>
              <div v-if="errors.range_penghasilan" class="text-danger small mt-1">{{ errors.range_penghasilan }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Status Mencari Nafkah <span class="text-danger">*</span></label>
              <div class="d-flex flex-column gap-2">
                <AppRadio v-for="opt in pencariNafkahOpts" :key="opt.value"
                  v-model="form.status_pencari_nafkah" :value="opt.value" :label="opt.label" />
              </div>
              <div v-if="errors.status_pencari_nafkah" class="text-danger small mt-1">{{ errors.status_pencari_nafkah }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Catatan -->
      <div class="card mb-4">
        <div class="card-body">
          <AppTextarea v-model="form.catatan" label="Catatan (opsional)" rows="3" :error="errors.catatan" />
        </div>
      </div>
    </template>

    <!-- Actions -->
    <div class="d-flex gap-2 justify-content-end">
      <a :href="cancelUrl" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary" :disabled="processing">
        <span v-if="processing" class="spinner-border spinner-border-sm me-1" />
        {{ submitLabel }}
      </button>
    </div>

  </form>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import WilayahCascade from '@/Components/UI/WilayahCascade.vue'
import { AppInput, AppRadio, AppTextarea } from '@/Components/UI/Form'

const props = defineProps<{
  form: Record<string, any>
  errors: Record<string, string>
  processing: boolean
  cancelUrl: string
  submitLabel?: string
  readonlyWilayah?: boolean
  mode?: 'gampong' | 'admin'
}>()

const emit = defineEmits<{
  'submit': []
  'update:form': [form: Record<string, any>]
}>()

const wilayah = computed({
  get: () => ({
    kode_provinsi:  props.form.kode_provinsi,
    kode_kabupaten: props.form.kode_kabupaten,
    kode_kecamatan: props.form.kode_kecamatan,
    kode_desa:      props.form.kode_desa,
    nama_desa:      props.form.nama_desa,
    nama_kecamatan: props.form.nama_kecamatan,
    nama_kabupaten: props.form.nama_kabupaten,
    nama_provinsi:  props.form.nama_provinsi,
  }),
  set: (val) => {
    emit('update:form', { ...props.form, ...val })
  },
})

const statusPernikahanOpts = [
  { value: 'kawin',       label: 'Kawin' },
  { value: 'belum_kawin', label: 'Belum Kawin' },
  { value: 'janda',       label: 'Janda' },
  { value: 'duda',        label: 'Duda' },
]

const jumlahOpts = [
  { value: 'tidak_ada', label: 'Tidak Ada' },
  { value: '1-2',       label: '1–2 orang' },
  { value: '2-5',       label: '2–5 orang' },
  { value: 'lebih_5',   label: 'Lebih dari 5 orang' },
]

const penghasilanOpts = [
  { value: 'tidak_ada',        label: 'Tidak memiliki penghasilan' },
  { value: 'kurang_1500000',   label: 'Kurang dari Rp 1.500.000' },
  { value: '1500000_2500000',  label: 'Rp 1.500.000 s/d Rp 2.500.000' },
  { value: 'lebih_2500000',    label: 'Lebih dari Rp 2.500.000' },
]

const pencariNafkahOpts = [
  { value: 'utama',         label: 'Pencari nafkah utama' },
  { value: 'sampingan',     label: 'Pencari nafkah sampingan' },
  { value: 'tidak_mencari', label: 'Tidak mencari nafkah (bergantung pada pemberian keluarga)' },
]
</script>
