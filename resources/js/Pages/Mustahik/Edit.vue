<template>
  <AppLayout title="Edit Mustahik">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Mustahik', href: backUrl }, { label: mustahik.nama_lengkap, href: backUrl }, { label: 'Edit' }]" />
    </template>

    <MustahikForm
      :form="form"
      :errors="errors"
      :processing="processing"
      :cancel-url="backUrl"
      submit-label="Perbarui Data"
      :readonly-wilayah="!!readonly?.length"
      :mode="mode ?? 'admin'"
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
  mustahik: any
  backUrl: string
  readonly?: string[]
  mode?: 'gampong' | 'admin'
}>()

// Determine which route to use for update
const isAdminRoute = props.backUrl.includes('/admin/')
const updateRoute = isAdminRoute
  ? route('admin.mustahik.update', props.mustahik.id)
  : route('gampong.mustahik.update', props.mustahik.id)

const baseFields = {
  kode_provinsi:     props.mustahik.kode_provinsi,
  kode_kabupaten:    props.mustahik.kode_kabupaten,
  kode_kecamatan:    props.mustahik.kode_kecamatan,
  kode_desa:         props.mustahik.kode_desa,
  nama_provinsi:     props.mustahik.provinsi?.nama,
  nama_kabupaten:    props.mustahik.kabupaten?.nama,
  nama_kecamatan:    props.mustahik.kecamatan?.nama,
  nama_desa:         props.mustahik.desa?.nama,
  nama_lengkap:      props.mustahik.nama_lengkap,
  tempat_lahir:      props.mustahik.tempat_lahir,
  tanggal_lahir:     props.mustahik.tanggal_lahir,
  jenis_kelamin:     props.mustahik.jenis_kelamin,
  nik:               props.mustahik.nik ?? '',
  alamat_gampong:    props.mustahik.alamat_gampong,
  alamat_dusun:      props.mustahik.alamat_dusun ?? '',
  no_hp:             props.mustahik.no_hp ?? '',
  pekerjaan:         props.mustahik.pekerjaan,
  status_pernikahan: props.mustahik.status_pernikahan,
}

const adminOnlyFields = props.mode !== 'gampong' ? {
  pemilik_rekening:     props.mustahik.pemilik_rekening ?? '',
  nomor_rekening:       props.mustahik.nomor_rekening ?? '',
  jumlah_anggota:       props.mustahik.jumlah_anggota ?? '',
  jumlah_tanggungan:    props.mustahik.jumlah_tanggungan ?? '',
  range_penghasilan:    props.mustahik.range_penghasilan ?? '',
  status_pencari_nafkah: props.mustahik.status_pencari_nafkah ?? '',
  catatan:              props.mustahik.catatan ?? '',
} : {}

const form = useForm({ ...baseFields, ...adminOnlyFields })

const errors = form.errors
const processing = form.processing

function updateForm(data: any) { Object.assign(form, data) }
function submit() { form.put(updateRoute) }
</script>
