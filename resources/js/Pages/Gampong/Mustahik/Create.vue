<template>
  <AppLayout title="Tambah Mustahik">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Mustahik', href: route('gampong.mustahik.index') }, { label: 'Tambah' }]" />
    </template>

    <!-- Info wilayah (readonly) -->
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
      <i class="ti ti-map-pin fs-5"></i>
      <div>
        Mustahik akan didaftarkan untuk:
        <strong>{{ wilayah.nama_desa }}</strong> — {{ wilayah.nama_kecamatan }}, {{ wilayah.nama_kabupaten }}
      </div>
    </div>

    <MustahikForm
      :form="form"
      :errors="errors"
      :processing="processing"
      :cancel-url="route('gampong.mustahik.index')"
      submit-label="Simpan Mustahik"
      :readonly-wilayah="true"
      mode="gampong"
      @submit="submit"
      @update:form="updateForm"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { AppBreadcrumb } from '@/Components/UI'
import MustahikForm from '@/Components/Mustahik/MustahikForm.vue'

const props = defineProps<{
  wilayah: {
    kode_desa: string; nama_desa: string
    kode_kecamatan: string; nama_kecamatan: string
    kode_kabupaten: string; nama_kabupaten: string
    kode_provinsi: string; nama_provinsi: string
  }
}>()

const form = useForm({
  ...props.wilayah,
  nama_lengkap: '', tempat_lahir: '', tanggal_lahir: '',
  jenis_kelamin: '', nik: '', alamat_gampong: '', alamat_dusun: '',
  no_hp: '', pekerjaan: '', status_pernikahan: '',
})

const errors     = form.errors
const processing = form.processing

function updateForm(data: any) { Object.assign(form, data) }
function submit() { form.post(route('gampong.mustahik.store')) }
</script>
