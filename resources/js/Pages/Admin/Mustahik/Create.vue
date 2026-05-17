<template>
  <AppLayout title="Tambah Mustahik">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Mustahik', href: route('admin.mustahik.index') }, { label: 'Tambah' }]" />
    </template>

    <MustahikForm
      :form="form"
      :errors="errors"
      :processing="processing"
      :cancel-url="route('admin.mustahik.index')"
      submit-label="Simpan Mustahik"
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

defineProps<{ kecamatan: any[] }>()

const form = useForm({
  kode_provinsi: '11', nama_provinsi: 'Aceh',
  kode_kabupaten: '11.12', nama_kabupaten: 'Kabupaten Aceh Barat Daya',
  kode_kecamatan: '', nama_kecamatan: '',
  kode_desa: '', nama_desa: '',
  nama_lengkap: '', tempat_lahir: '', tanggal_lahir: '',
  jenis_kelamin: '', nik: '', alamat_gampong: '', alamat_dusun: '',
  no_hp: '', pemilik_rekening: '', nomor_rekening: '',
  pekerjaan: '', status_pernikahan: '',
  jumlah_anggota: '', jumlah_tanggungan: '',
  range_penghasilan: '', status_pencari_nafkah: '',
  catatan: '',
})

const errors     = form.errors
const processing = form.processing

function updateForm(data: any) {
  Object.assign(form, data)
}

function submit() {
  form.post(route('admin.mustahik.store'))
}
</script>
