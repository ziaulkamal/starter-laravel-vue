<template>
  <AppLayout title="Data Mustahik">
    <template #breadcrumb>
      <AppBreadcrumb :items="[{ label: 'Mustahik' }]" />
    </template>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <h5 class="mb-0">Daftar Mustahik</h5>
        <Link :href="route('admin.mustahik.create')" class="btn btn-primary btn-sm" v-if="can('mustahik.create')">
          + Tambah Mustahik
        </Link>
      </div>

      <!-- Filters -->
      <div class="card-body border-bottom pb-3">
        <div class="row g-2">
          <div class="col-md-4">
            <input v-model="filterForm.search" type="search" class="form-control form-control-sm"
              placeholder="Cari nama atau NIK..." @input="debouncedFilter" />
          </div>
          <div class="col-md-3">
            <select v-model="filterForm.kode_kecamatan" class="form-select form-select-sm" @change="applyFilter">
              <option value="">Semua Kecamatan</option>
              <option v-for="kec in kecamatan" :key="kec.kode" :value="kec.kode">{{ kec.nama }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="filterForm.status" class="form-select form-select-sm" @change="applyFilter">
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="filterForm.jenis_kelamin" class="form-select form-select-sm" @change="applyFilter">
              <option value="">Semua JK</option>
              <option value="laki-laki">Laki-laki</option>
              <option value="perempuan">Perempuan</option>
            </select>
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary btn-sm w-100" @click="resetFilter">Reset</button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nama Lengkap</th>
              <th>NIK</th>
              <th>Gampong / Kecamatan</th>
              <th>Status Nikah</th>
              <th>Penghasilan</th>
              <th>Status</th>
              <th>Berkas</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="mustahik.data.length === 0">
              <td colspan="9" class="text-center text-muted py-5">Tidak ada data mustahik.</td>
            </tr>
            <tr v-for="(item, i) in mustahik.data" :key="item.id">
              <td class="text-muted small">{{ mustahik.from + i }}</td>
              <td>
                <Link :href="route('admin.mustahik.show', item.id)" class="fw-medium text-decoration-none">
                  {{ item.nama_lengkap }}
                </Link>
                <div class="text-muted small">{{ item.tempat_lahir }}, {{ formatDate(item.tanggal_lahir) }}</div>
              </td>
              <td class="small font-monospace">{{ item.nik || '-' }}</td>
              <td class="small">
                <div>{{ item.desa?.nama }}</div>
                <div class="text-muted">{{ item.kecamatan?.nama }}</div>
              </td>
              <td class="small">{{ labelStatusNikah(item.status_pernikahan) }}</td>
              <td class="small">{{ labelPenghasilan(item.range_penghasilan) }}</td>
              <td>
                <span class="badge" :class="item.status === 'aktif' ? 'bg-success' : 'bg-danger'">
                  {{ item.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="small text-center">
                <span class="badge bg-secondary">{{ item.berkas_files_count }}</span>
              </td>
              <td class="text-end">
                <Link :href="route('admin.mustahik.show', item.id)" class="btn btn-sm btn-outline-primary">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center small text-muted">
        <span>Menampilkan {{ mustahik.from }}–{{ mustahik.to }} dari {{ mustahik.total }} mustahik</span>
        <div class="d-flex gap-1">
          <Link v-if="mustahik.prev_page_url" :href="mustahik.prev_page_url" class="btn btn-sm btn-outline-secondary" preserve-scroll>
            &laquo; Sebelumnya
          </Link>
          <Link v-if="mustahik.next_page_url" :href="mustahik.next_page_url" class="btn btn-sm btn-outline-secondary" preserve-scroll>
            Berikutnya &raquo;
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { AppBreadcrumb } from '@/Components/UI'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps<{
  mustahik: any
  filters: Record<string, string>
  kecamatan: { kode: string; nama: string }[]
}>()

const page = usePage()
const can = (perm: string) => (page.props.auth as any).permissions.includes(perm)

const filterForm = reactive({ ...props.filters })

function applyFilter() {
  router.get(route('admin.mustahik.index'), filterForm, { preserveState: true, replace: true })
}

const debouncedFilter = useDebounceFn(applyFilter, 400)

function resetFilter() {
  Object.keys(filterForm).forEach(k => (filterForm as any)[k] = '')
  applyFilter()
}

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('id-ID') : '-'

const labelStatusNikah = (v: string) => ({
  kawin: 'Kawin', belum_kawin: 'Belum Kawin', janda: 'Janda', duda: 'Duda',
}[v] ?? v)

const labelPenghasilan = (v: string) => ({
  tidak_ada: 'Tidak ada',
  kurang_1500000: '< Rp 1,5 jt',
  '1500000_2500000': 'Rp 1,5–2,5 jt',
  lebih_2500000: '> Rp 2,5 jt',
}[v] ?? v)
</script>
