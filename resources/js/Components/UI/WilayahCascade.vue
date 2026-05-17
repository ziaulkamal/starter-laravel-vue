<template>
  <div class="row g-3">
    <div v-if="!hideProvinsi" :class="colClass">
      <label class="form-label">Provinsi <span class="text-danger">*</span></label>
      <input type="text" class="form-control" :value="modelValue.nama_provinsi || 'Aceh'" readonly disabled />
      <input type="hidden" :name="fieldNames.provinsi" :value="modelValue.kode_provinsi" />
    </div>

    <div v-if="!hideKabupaten" :class="colClass">
      <label class="form-label">Kabupaten <span class="text-danger">*</span></label>
      <input type="text" class="form-control" :value="modelValue.nama_kabupaten || 'Kabupaten Aceh Barat Daya'" readonly disabled />
      <input type="hidden" :name="fieldNames.kabupaten" :value="modelValue.kode_kabupaten" />
    </div>

    <div :class="colClass">
      <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
      <select
        class="form-select"
        :class="{ 'is-invalid': errors?.kode_kecamatan }"
        :value="modelValue.kode_kecamatan"
        :disabled="readonly"
        @change="onKecamatanChange($event.target.value)"
      >
        <option value="">-- Pilih Kecamatan --</option>
        <option v-for="kec in kecamatanList" :key="kec.kode" :value="kec.kode">
          {{ kec.nama }}
        </option>
      </select>
      <div v-if="errors?.kode_kecamatan" class="invalid-feedback">{{ errors.kode_kecamatan }}</div>
    </div>

    <div :class="colClass">
      <label class="form-label">Desa/Gampong <span class="text-danger">*</span></label>
      <select
        class="form-select"
        :class="{ 'is-invalid': errors?.kode_desa }"
        :value="modelValue.kode_desa"
        :disabled="readonly || !modelValue.kode_kecamatan"
        @change="onDesaChange($event.target.value)"
      >
        <option value="">-- Pilih Desa --</option>
        <option v-for="d in desaList" :key="d.kode" :value="d.kode">
          {{ d.nama }}
        </option>
      </select>
      <div v-if="errors?.kode_desa" class="invalid-feedback">{{ errors.kode_desa }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

interface WilayahItem { kode: string; nama: string }
interface WilayahValue {
  kode_provinsi?: string
  nama_provinsi?: string
  kode_kabupaten?: string
  nama_kabupaten?: string
  kode_kecamatan?: string
  nama_kecamatan?: string
  kode_desa?: string
  nama_desa?: string
}

const props = withDefaults(defineProps<{
  modelValue: WilayahValue
  readonly?: boolean
  hideProvinsi?: boolean
  hideKabupaten?: boolean
  colClass?: string
  errors?: Record<string, string>
  fieldNames?: { provinsi: string; kabupaten: string; kecamatan: string; desa: string }
}>(), {
  readonly: false,
  hideProvinsi: false,
  hideKabupaten: false,
  colClass: 'col-md-6',
  fieldNames: () => ({
    provinsi: 'kode_provinsi',
    kabupaten: 'kode_kabupaten',
    kecamatan: 'kode_kecamatan',
    desa: 'kode_desa',
  }),
})

const emit = defineEmits<{
  'update:modelValue': [value: WilayahValue]
}>()

const kecamatanList = ref<WilayahItem[]>([])
const desaList      = ref<WilayahItem[]>([])

async function loadKecamatan(parentKab = '11.12') {
  const { data } = await axios.get('/api/wilayah/kecamatan', { params: { parent: parentKab } })
  kecamatanList.value = data
}

async function loadDesa(kodeKec: string) {
  if (!kodeKec) { desaList.value = []; return }
  const { data } = await axios.get('/api/wilayah/desa', { params: { parent: kodeKec } })
  desaList.value = data
}

function onKecamatanChange(kodeKec: string) {
  const kec = kecamatanList.value.find(k => k.kode === kodeKec)
  emit('update:modelValue', {
    ...props.modelValue,
    kode_kecamatan: kodeKec,
    nama_kecamatan: kec?.nama,
    kode_desa: '',
    nama_desa: '',
  })
  loadDesa(kodeKec)
}

function onDesaChange(kodeDesa: string) {
  const desa = desaList.value.find(d => d.kode === kodeDesa)
  emit('update:modelValue', {
    ...props.modelValue,
    kode_desa:      kodeDesa,
    nama_desa:      desa?.nama,
    kode_provinsi:  kodeDesa ? kodeDesa.substring(0, 2) : '',
    kode_kabupaten: kodeDesa ? kodeDesa.substring(0, 5) : '',
  })
}

onMounted(async () => {
  await loadKecamatan(props.modelValue.kode_kabupaten || '11.12')
  if (props.modelValue.kode_kecamatan) {
    await loadDesa(props.modelValue.kode_kecamatan)
  }
})

watch(() => props.modelValue.kode_kabupaten, async (val) => {
  if (val) await loadKecamatan(val)
})
</script>
