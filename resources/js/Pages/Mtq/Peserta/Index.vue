<template>
    <AppLayout
        title="Pendaftaran Peserta"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Pendaftaran Peserta' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0 && !isUserRole"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeletePesertaModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <PesertaGeneratorModal
                v-if="isSuperadmin"
                :kafilahs="props.kafilahs"
                :cabangs="props.cabangs"
                :golongans="props.golongans"
                :kriteria="props.kriteria"
            />
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Daftar Peserta</span>
            </button>
        </template>

        <AppFlash />

        <!-- Status filter tabs -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                :class="['btn btn-sm', activeTab === tab.value ? `btn-${tab.color}` : 'btn-outline-secondary']"
                @click="activeTab = tab.value"
            >
                {{ tab.label }}
                <span
                    class="badge ms-1"
                    :class="activeTab === tab.value ? 'bg-white text-dark' : 'text-bg-secondary'"
                >{{ tab.count }}</span>
            </button>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="filteredPeserta"
                    :columns="columns"
                    :actions="{ view: true, edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    :row-can-view="rowCanView"
                    :row-can-edit="rowCanEdit"
                    :row-can-delete="rowCanDelete"
                    empty-text="Belum ada peserta terdaftar"
                    @view="(row) => openDetail(row)"
                    @edit="openEdit"
                    @delete="openDelete"
                >
                    <template #cell-jenis_kelamin="{ value }">
                        <span :class="['badge rounded-pill', value === 'L' ? 'text-bg-info' : 'text-bg-danger']">
                            {{ value === 'L' ? 'Putra' : 'Putri' }}
                        </span>
                    </template>

                    <template #cell-status="{ value, row }">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span :class="['badge rounded-pill', statusBadgeClass(value as string)]">
                                {{ statusLabel(value as string) }}
                            </span>

                            <!-- Catatan penolakan -->
                            <span
                                v-if="value === 'ditolak' && (row as PesertaRow).catatan_verifikasi"
                                class="ti ti-info-circle text-danger"
                                :title="(row as PesertaRow).catatan_verifikasi ?? ''"
                                style="cursor:help"
                            ></span>

                            <!-- Ajukan verifikasi (draft) -->
                            <button
                                v-if="value === 'draft'"
                                type="button"
                                class="btn btn-sm bg-info-subtle text-info app-action-btn"
                                title="Ajukan untuk verifikasi"
                                :disabled="submitProcessing === (row as PesertaRow).id"
                                @click="submitPeserta(row as PesertaRow)"
                            >
                                <span
                                    v-if="submitProcessing === (row as PesertaRow).id"
                                    class="spinner-border spinner-border-sm"
                                ></span>
                                <i v-else class="ti ti-send fs-5"></i>
                            </button>
                        </div>
                    </template>

                    <!-- Tombol aksi tambahan per-baris (di kolom Aksi) -->
                    <template #row-actions="{ row }">
                        <!-- Ajukan Edit: user role, ditolak atau diverifikasi -->
                        <template v-if="isUserRole && ((row as PesertaRow).status === 'ditolak' || (row as PesertaRow).status === 'diverifikasi')">
                            <button
                                v-if="!(row as PesertaRow).has_pending_pengajuan"
                                type="button"
                                class="btn btn-sm bg-warning-subtle text-warning app-action-btn"
                                :title="(row as PesertaRow).status === 'diverifikasi' ? 'Ajukan edit (perlu persetujuan superadmin)' : 'Ajukan permohonan edit'"
                                @click="openAjukanEdit(row as PesertaRow)"
                            >
                                <i class="ti ti-edit fs-5"></i>
                            </button>
                            <span
                                v-else
                                class="badge text-bg-warning small"
                                title="Sudah ada pengajuan edit yang menunggu"
                            >Edit Menunggu</span>
                        </template>

                        <!-- Ajukan Hapus: admin (bukan superadmin), diverifikasi -->
                        <template v-if="isAdmin && !isSuperadmin && (row as PesertaRow).status === 'diverifikasi'">
                            <button
                                v-if="!(row as PesertaRow).has_pending_hapus"
                                type="button"
                                class="btn btn-sm bg-danger-subtle text-danger app-action-btn"
                                title="Ajukan hapus peserta (perlu persetujuan superadmin)"
                                @click="openAjukanHapus(row as PesertaRow)"
                            >
                                <i class="ti ti-trash fs-5"></i>
                            </button>
                            <span
                                v-else
                                class="badge text-bg-danger small"
                                title="Sudah ada pengajuan hapus yang menunggu"
                            >Hapus Menunggu</span>
                        </template>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- ── Form Modal (Create / Edit) ────────────────────────────── -->
        <AppFormModal
            id="pesertaModal"
            size="xl"
            :title="isEditing ? 'Edit Data Peserta' : 'Daftar Peserta Baru'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Daftarkan'"
            @submit="submitForm"
        >
            <div class="row g-3">
                <!-- Kafilah (disembunyikan untuk role user — otomatis dari akun) -->
                <div v-if="!isUserRole" class="col-md-6">
                    <AppSelect2
                        v-model="form.kafilah_id"
                        :options="kafilahOptions"
                        label="Kafilah (Kabupaten/Kota)"
                        placeholder="Pilih kafilah..."
                        search-placeholder="Cari kafilah..."
                        :error="form.errors.kafilah_id"
                        required
                    />
                </div>
                <!-- Cabang -->
                <div :class="isUserRole ? 'col-md-6' : 'col-md-6'">
                    <AppSelect
                        v-model="form.cabang_id"
                        :options="cabangOptions"
                        label="Cabang Lomba"
                        placeholder="— Pilih Cabang —"
                        :error="form.errors.cabang_id"
                        required
                        @change="() => (form.golongan_id = '')"
                    />
                </div>
                <!-- Golongan -->
                <div class="col-md-6">
                    <AppSelect
                        v-model="form.golongan_id"
                        :options="filteredGolonganOptions"
                        label="Golongan"
                        placeholder="— Pilih Golongan —"
                        :error="form.errors.golongan_id"
                        :disabled="!form.cabang_id"
                        required
                    />
                </div>
                <!-- Jenis Kelamin -->
                <div class="col-md-6">
                    <AppSelect
                        v-model="form.jenis_kelamin"
                        :options="jkOptions"
                        label="Jenis Kelamin"
                        placeholder="— Pilih —"
                        :error="form.errors.jenis_kelamin"
                        required
                    />
                </div>
                <!-- Nama -->
                <div class="col-12">
                    <AppInput
                        v-model="form.nama"
                        label="Nama Lengkap"
                        placeholder="Nama sesuai akta/dokumen resmi"
                        :error="form.errors.nama"
                        required
                    />
                </div>
                <!-- NIK + Tgl Lahir -->
                <div class="col-md-6">
                    <AppInput
                        v-model="form.nik"
                        label="NIK"
                        placeholder="16 digit NIK"
                        :error="form.errors.nik"
                    />
                </div>
                <div class="col-md-6">
                    <AppInput
                        v-model="form.tgl_lahir"
                        type="date"
                        label="Tanggal Lahir"
                        :error="form.errors.tgl_lahir"
                    />
                </div>
                <!-- Tempat Lahir -->
                <div class="col-12">
                    <AppInput
                        v-model="form.tempat_lahir"
                        label="Tempat Lahir"
                        placeholder="Kota/kabupaten tempat lahir"
                        :error="form.errors.tempat_lahir"
                    />
                </div>
                <!-- Alamat -->
                <div class="col-12">
                    <AppTextarea
                        v-model="form.alamat"
                        label="Alamat"
                        placeholder="Alamat domisili peserta"
                        :error="form.errors.alamat"
                        :rows="2"
                    />
                </div>

                <!-- Wilayah cascading -->
                <div class="col-12">
                    <p class="form-label fw-medium mb-1">Wilayah Domisili</p>
                </div>
                <div class="col-md-6">
                    <AppSelect2
                        v-model="wilayah.kodeProvinsi"
                        :options="wilayah.optProvinsi"
                        label="Provinsi"
                        placeholder="Pilih provinsi..."
                        search-placeholder="Cari provinsi..."
                        @update:model-value="wilayah.onProvinsiChange"
                    />
                </div>
                <div class="col-md-6">
                    <AppSelect2
                        v-model="wilayah.kodeKabupaten"
                        :options="wilayah.optKabupaten"
                        label="Kabupaten/Kota"
                        placeholder="Pilih kabupaten..."
                        search-placeholder="Cari kabupaten..."
                        :disabled="wilayah.loadingKabupaten || !wilayah.kodeProvinsi"
                        @update:model-value="wilayah.onKabupatenChange"
                    />
                </div>
                <div class="col-md-6">
                    <AppSelect2
                        v-model="wilayah.kodeKecamatan"
                        :options="wilayah.optKecamatan"
                        label="Kecamatan"
                        placeholder="Pilih kecamatan..."
                        search-placeholder="Cari kecamatan..."
                        :disabled="wilayah.loadingKecamatan || !wilayah.kodeKabupaten"
                        @update:model-value="wilayah.onKecamatanChange"
                    />
                </div>
                <div class="col-md-6">
                    <AppSelect2
                        v-model="wilayah.kodeDesa"
                        :options="wilayah.optDesa"
                        label="Desa/Kelurahan"
                        placeholder="Pilih desa..."
                        search-placeholder="Cari desa..."
                        :disabled="wilayah.loadingDesa || !wilayah.kodeKecamatan"
                        :error="form.errors.kode_wilayah_desa"
                    />
                </div>
            </div>

            <!-- Lampiran — edit mode only -->
            <template v-if="isEditing">
                <hr class="my-4">

                <!-- ── Pas Foto ─────────────────────────────────────────── -->
                <h6 class="fw-semibold mb-3">
                    <i class="ti ti-camera me-1"></i>Pas Foto Peserta
                </h6>
                <div class="border rounded-3 p-3 bg-body-tertiary mb-4">
                    <div class="d-flex align-items-start gap-3">

                        <!-- Preview 3×4 -->
                        <div class="flex-shrink-0 text-center">
                            <img
                                v-if="editingRow?.foto_url"
                                :src="editingRow.foto_url"
                                class="rounded border"
                                style="width:90px;height:120px;object-fit:cover;display:block"
                                alt="Pas foto"
                            >
                            <div
                                v-else
                                class="rounded border bg-body-secondary d-flex flex-column align-items-center justify-content-center"
                                style="width:90px;height:120px"
                            >
                                <i class="ti ti-user-circle text-muted" style="font-size:2.5rem"></i>
                                <span class="text-muted mt-1" style="font-size:0.6rem">Belum ada</span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:0.65rem">3 × 4</div>
                        </div>

                        <!-- Upload controls -->
                        <div class="flex-grow-1">
                            <div class="mb-2 small text-muted lh-sm">
                                <span class="fw-semibold text-body">Ketentuan pas foto:</span><br>
                                · Format <strong>JPG</strong> atau <strong>PNG</strong><br>
                                · Rasio <strong>3×4</strong> (portrait), min. 300×400 px<br>
                                · Ukuran maksimal <strong>2 MB</strong>
                            </div>
                            <div class="d-flex gap-2">
                                <input
                                    ref="fotoInputRef"
                                    type="file"
                                    class="form-control form-control-sm"
                                    accept=".jpg,.jpeg,.png"
                                    @change="onFotoChange"
                                >
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary flex-shrink-0"
                                    :disabled="!fotoForm.foto || fotoForm.processing"
                                    @click="uploadFoto"
                                >
                                    <span v-if="fotoForm.processing" class="spinner-border spinner-border-sm"></span>
                                    <span v-else>Simpan Foto</span>
                                </button>
                            </div>
                            <div v-if="fotoError" class="alert alert-danger py-2 small mt-2 mb-0 d-flex align-items-center gap-2">
                                <i class="ti ti-alert-circle flex-shrink-0"></i>
                                <span>{{ fotoError }}</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Berkas Dokumen ───────────────────────────────────── -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ti ti-files me-1"></i>Berkas Dokumen
                    </h6>
                    <span class="badge text-bg-secondary rounded-pill">
                        {{ editingRow?.berkas?.length ?? 0 }} file
                    </span>
                </div>

                <!-- Existing berkas list -->
                <div v-if="editingRow?.berkas?.length" class="list-group mb-3">
                    <div
                        v-for="b in editingRow.berkas"
                        :key="b.id"
                        class="list-group-item d-flex align-items-center gap-2 py-2"
                    >
                        <i :class="['ti fs-4 text-muted', fileIcon(b.mime_type)]"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-medium">{{ berkasLabel(b.jenis) }}</div>
                            <a
                                :href="b.url"
                                target="_blank"
                                class="small text-muted text-truncate d-block"
                                style="max-width: 300px"
                            >{{ b.nama_asli }}</a>
                        </div>
                        <span v-if="b.ukuran" class="small text-muted text-nowrap">
                            {{ formatSize(b.ukuran) }}
                        </span>
                        <button
                            type="button"
                            class="btn btn-sm btn-link text-danger p-0 ms-1"
                            title="Hapus berkas"
                            @click="deleteBerkas(b.id)"
                        >
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>
                </div>
                <p v-else class="text-muted small fst-italic mb-3">Belum ada berkas diunggah.</p>

                <!-- Upload berkas baru -->
                <div class="border rounded-3 p-3 bg-body-tertiary">
                    <p class="small fw-semibold text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em">
                        Upload Berkas Baru
                    </p>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <AppSelect
                                v-model="berkasForm.jenis"
                                :options="berkasJenisOptions"
                                placeholder="— Jenis Berkas —"
                                size="sm"
                            />
                        </div>
                        <div class="col-md-6">
                            <input
                                ref="fileInputRef"
                                type="file"
                                class="form-control form-control-sm"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                @change="onFileChange"
                            />
                        </div>
                        <div class="col-md-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-primary w-100"
                                :disabled="!berkasForm.jenis || !berkasForm.file || berkasForm.processing"
                                @click="uploadBerkas"
                            >
                                <span
                                    v-if="berkasForm.processing"
                                    class="spinner-border spinner-border-sm"
                                ></span>
                                <span v-else>Upload</span>
                            </button>
                        </div>
                    </div>
                    <div v-if="berkasError" class="alert alert-danger py-2 small mb-0 mt-2 d-flex align-items-center gap-2">
                        <i class="ti ti-alert-circle flex-shrink-0"></i>
                        <span>{{ berkasError }}</span>
                    </div>
                    <p v-else class="form-text mb-0 mt-1">
                        Format: PDF, JPG, PNG, DOC, DOCX · Maks. 5 MB
                    </p>
                </div>
            </template>
        </AppFormModal>

        <!-- ── Ajukan Edit Modal ───────────────────────────────────────── -->
        <AppFormModal
            id="ajukanEditModal"
            size="sm"
            title="Ajukan Permohonan Edit"
            :processing="ajukanEditForm.processing"
            submit-label="Kirim Permohonan"
            @submit="doAjukanEdit"
        >
            <p class="mb-3 text-muted small">
                Permohonan akan dikirim ke admin untuk disetujui. Peserta berstatus
                <strong>diverifikasi</strong> hanya dapat disetujui oleh superadmin.
            </p>
            <p class="mb-3">
                Peserta: <strong>{{ ajukanEditTarget?.nama }}</strong>
                <span :class="['badge ms-1 rounded-pill', statusBadgeClass(ajukanEditTarget?.status ?? '')]">
                    {{ statusLabel(ajukanEditTarget?.status ?? '') }}
                </span>
            </p>
            <AppTextarea
                v-model="ajukanEditForm.pesan"
                label="Alasan / Pesan Permohonan"
                placeholder="Jelaskan alasan mengapa data perlu diedit..."
                :error="ajukanEditForm.errors.pesan"
                :rows="3"
                required
            />
        </AppFormModal>

        <!-- ── Ajukan Hapus Modal (admin → superadmin) ────────────────── -->
        <AppFormModal
            id="ajukanHapusModal"
            size="sm"
            title="Ajukan Permohonan Hapus"
            :processing="ajukanHapusForm.processing"
            submit-label="Kirim Permohonan"
            submit-class="btn-danger"
            @submit="doAjukanHapus"
        >
            <div class="alert alert-warning py-2 small mb-3">
                <i class="ti ti-alert-triangle me-1"></i>
                Permohonan hapus akan dikirim ke superadmin untuk disetujui. Jika disetujui, data peserta akan dihapus permanen.
            </div>
            <p class="mb-3">
                Peserta: <strong>{{ ajukanHapusTarget?.nama }}</strong>
                <span class="badge ms-1 rounded-pill text-bg-success">Terverifikasi</span>
            </p>
            <AppTextarea
                v-model="ajukanHapusForm.pesan"
                label="Alasan Permohonan Hapus"
                placeholder="Jelaskan alasan mengapa peserta ini perlu dihapus..."
                :error="ajukanHapusForm.errors.pesan"
                :rows="3"
                required
            />
        </AppFormModal>

        <!-- ── Verify Modal ───────────────────────────────────────────── -->
        <AppFormModal
            id="verifyPesertaModal"
            size="sm"
            title="Verifikasi Peserta"
            :processing="verifyForm.processing"
            submit-label="Verifikasi"
            @submit="doVerify"
        >
            <p class="mb-3">
                Verifikasi <strong>{{ verifyTarget?.nama }}</strong>?
            </p>
            <AppInput
                v-model="verifyForm.nomor_peserta"
                label="Nomor Peserta"
                placeholder="cth: 2025/001"
                :error="verifyForm.errors.nomor_peserta"
                required
            />
        </AppFormModal>

        <!-- ── Reject Modal ───────────────────────────────────────────── -->
        <AppFormModal
            id="rejectPesertaModal"
            size="sm"
            title="Tolak Pendaftaran"
            :processing="rejectForm.processing"
            submit-label="Simpan & Tolak"
            @submit="doReject"
        >
            <p class="mb-3">
                Tolak pendaftaran <strong>{{ rejectTarget?.nama }}</strong>?
            </p>
            <AppTextarea
                v-model="rejectForm.catatan_verifikasi"
                label="Alasan Penolakan"
                placeholder="Tuliskan alasan penolakan..."
                :error="rejectForm.errors.catatan_verifikasi"
                :rows="3"
                required
            />
        </AppFormModal>

        <!-- ── Delete Single ──────────────────────────────────────────── -->
        <AppDeleteModal
            id="deletePesertaModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Peserta <strong>{{ deleteTarget?.nama }}</strong> beserta seluruh berkasnya akan dihapus permanen.
        </AppDeleteModal>

        <!-- ── Bulk Delete ─────────────────────────────────────────────── -->
        <AppDeleteModal
            id="bulkDeletePesertaModal"
            title="Hapus Peserta Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} peserta</strong> beserta seluruh berkasnya akan dihapus permanen.
            Tindakan ini tidak dapat dibatalkan.
        </AppDeleteModal>

        <!-- ── Detail Peserta Modal ────────────────────────────────────── -->
        <div class="modal fade" id="detailPesertaModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            <i class="ti ti-id-badge-2"></i>
                            Detail Peserta
                        </h5>
                        <button type="button" class="btn-close" @click="hide('detailPesertaModal')"></button>
                    </div>

                    <div class="modal-body">
                        <div v-if="detailTarget" class="row g-4">

                            <!-- Foto + ringkasan -->
                            <div class="col-md-3 text-center">
                                <img
                                    v-if="detailTarget.foto_url"
                                    :src="detailTarget.foto_url"
                                    class="img-thumbnail mb-3 w-100"
                                    style="max-height:220px;object-fit:cover"
                                    alt="Foto peserta"
                                >
                                <div
                                    v-else
                                    class="border rounded d-flex align-items-center justify-content-center mb-3 bg-body-secondary"
                                    style="height:160px"
                                >
                                    <i class="ti ti-user-circle text-muted" style="font-size:3.5rem"></i>
                                </div>
                                <div class="fw-semibold mb-1">{{ detailTarget.nama }}</div>
                                <span :class="['badge rounded-pill', statusBadgeClass(detailTarget.status)]">
                                    {{ statusLabel(detailTarget.status) }}
                                </span>
                                <div v-if="detailTarget.nomor_peserta" class="small text-muted mt-1">
                                    No. {{ detailTarget.nomor_peserta }}
                                </div>
                            </div>

                            <!-- Data lengkap -->
                            <div class="col-md-9">
                                <h6 class="fw-semibold text-uppercase text-muted small mb-2" style="letter-spacing:.05em">
                                    Informasi Lomba
                                </h6>
                                <table class="table table-sm table-borderless mb-3 detail-info-table">
                                    <tbody>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0" style="width:35%">Kafilah</th>
                                            <td class="fw-medium">{{ detailTarget.kafilah_nama ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Cabang</th>
                                            <td>{{ detailTarget.cabang_nama ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Golongan</th>
                                            <td>{{ detailTarget.golongan_nama ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h6 class="fw-semibold text-uppercase text-muted small mb-2" style="letter-spacing:.05em">
                                    Data Diri
                                </h6>
                                <table class="table table-sm table-borderless mb-0 detail-info-table">
                                    <tbody>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0" style="width:35%">NIK</th>
                                            <td class="font-monospace">{{ detailTarget.nik ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Jenis Kelamin</th>
                                            <td>
                                                <span :class="['badge rounded-pill', detailTarget.jenis_kelamin === 'L' ? 'text-bg-info' : 'text-bg-danger']">
                                                    {{ detailTarget.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Tempat Lahir</th>
                                            <td>{{ detailTarget.tempat_lahir ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Tanggal Lahir</th>
                                            <td>{{ detailTarget.tgl_lahir ? formatDate(detailTarget.tgl_lahir) : '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Alamat</th>
                                            <td>{{ detailTarget.alamat ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted fw-normal ps-0">Kode Wilayah Desa</th>
                                            <td class="font-monospace small">{{ detailTarget.kode_wilayah_desa ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Berkas dokumen -->
                            <div class="col-12">
                                <hr class="mt-0">
                                <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                                    <i class="ti ti-files"></i>Berkas Dokumen
                                    <span :class="['badge rounded-pill ms-1', detailTarget.berkas?.length ? 'text-bg-primary' : 'text-bg-secondary']">
                                        {{ detailTarget.berkas?.length ?? 0 }}
                                    </span>
                                </h6>

                                <div v-if="detailTarget.berkas?.length" class="row g-2">
                                    <div
                                        v-for="b in detailTarget.berkas"
                                        :key="b.id"
                                        class="col-md-6"
                                    >
                                        <a
                                            :href="b.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="d-flex align-items-center gap-3 p-3 border rounded text-decoration-none text-body detail-berkas-item"
                                        >
                                            <i :class="['ti flex-shrink-0 text-muted', fileIcon(b.mime_type)]" style="font-size:2rem"></i>
                                            <div class="overflow-hidden flex-grow-1">
                                                <div class="small fw-semibold">{{ berkasLabel(b.jenis) }}</div>
                                                <div class="text-muted small text-truncate">{{ b.nama_asli }}</div>
                                                <div v-if="b.ukuran" class="text-muted" style="font-size:0.7rem">
                                                    {{ formatSize(b.ukuran) }}
                                                </div>
                                            </div>
                                            <i class="ti ti-external-link text-muted flex-shrink-0"></i>
                                        </a>
                                    </div>
                                </div>

                                <div v-else class="alert alert-warning py-2 small d-flex align-items-center gap-2 mb-0">
                                    <i class="ti ti-alert-triangle flex-shrink-0"></i>
                                    <span>Belum ada berkas yang diunggah. Peserta belum melampirkan dokumen pendukung.</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary me-auto"
                            @click="hide('detailPesertaModal')"
                        >
                            Tutup
                        </button>
                        <template v-if="detailTarget?.status === 'diajukan' && canVerifyReject">
                            <button
                                type="button"
                                class="btn btn-danger"
                                @click="openRejectFromDetail"
                            >
                                <i class="ti ti-x me-1"></i>Tolak Pendaftaran
                            </button>
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="openVerifyFromDetail"
                            >
                                <i class="ti ti-check me-1"></i>Verifikasi Peserta
                            </button>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch, onMounted, onUnmounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { useWilayahSelect } from '@/Composables/useWilayahSelect';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import AppFormModal from '@/Components/UI/AppFormModal.vue';
import AppDeleteModal from '@/Components/UI/AppDeleteModal.vue';
import AppInput from '@/Components/UI/Form/AppInput.vue';
import AppSelect from '@/Components/UI/Form/AppSelect.vue';
import AppSelect2 from '@/Components/UI/Form/AppSelect2.vue';
import AppTextarea from '@/Components/UI/Form/AppTextarea.vue';
import { useBootstrapModal } from '@/Composables/useBootstrapModal';
import PesertaGeneratorModal from '@/Components/Mtq/PesertaGeneratorModal.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';
import type { Select2Option } from '@/Components/UI/Form/AppSelect2.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface BerkasRow {
    id: number;
    jenis: string;
    url: string;
    nama_asli: string;
    mime_type: string | null;
    ukuran: number | null;
}

interface PesertaRow {
    [key: string]: unknown;
    id: number;
    kafilah_id: number;
    kafilah_nama: string | null;
    cabang_id: number;
    cabang_nama: string | null;
    golongan_id: number;
    golongan_nama: string | null;
    nama: string;
    nik: string | null;
    jenis_kelamin: 'L' | 'P';
    tempat_lahir: string | null;
    tgl_lahir: string | null;
    alamat: string | null;
    kode_wilayah_desa: string | null;
    foto_url: string | null;
    status: 'draft' | 'diajukan' | 'diverifikasi' | 'ditolak';
    catatan_verifikasi: string | null;
    nomor_peserta: string | null;
    has_pending_pengajuan: boolean;
    has_pending_hapus: boolean;
    berkas: BerkasRow[];
}

interface KafilahItem  { id: number; nama_kabupaten: string; }
interface CabangItem   { id: number; nama: string; }
interface GolonganItem { id: number; cabang_id: number; nama: string; jenis_kelamin: string; }
interface KriteriaItem { id: number; cabang_id: number; nama: string; }

interface PesertaForm {
    kafilah_id:        number | string;
    cabang_id:         number | string;
    golongan_id:       number | string;
    nama:              string;
    nik:               string;
    jenis_kelamin:     string;
    tempat_lahir:      string;
    tgl_lahir:         string;
    alamat:            string;
    kode_wilayah_desa: string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    peserta:       PesertaRow[];
    kafilahs:      KafilahItem[];
    cabangs:       CabangItem[];
    golongans:     GolonganItem[];
    kriteria:      KriteriaItem[];
    userKafilahId: number | null;
    userRole:      string;
}>();

// ── Auth / Role ───────────────────────────────────────────────────────────────

const page           = usePage();
const authRoles      = computed<string[]>(() => (page.props.auth as any)?.roles ?? []);
const isSuperadmin   = computed(() => authRoles.value.includes('superadmin'));
const isAdmin        = computed(() => authRoles.value.includes('admin'));
const isUserRole     = computed(() => props.userRole === 'user');
const canVerifyReject= computed(() => isSuperadmin.value || isAdmin.value);

// ── Wilayah cascading select ──────────────────────────────────────────────────

const wilayah = reactive(useWilayahSelect());

// ── Auto-refresh (polling + visibility) ──────────────────────────────────────

const POLL_INTERVAL = 45_000;
let pollTimer: ReturnType<typeof setInterval> | null = null;

function reloadPeserta(): void {
    router.reload({ only: ['peserta'] });
}

function startPolling(): void {
    if (pollTimer) return;
    pollTimer = setInterval(reloadPeserta, POLL_INTERVAL);
}

function stopPolling(): void {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function onVisibilityChange(): void {
    if (document.visibilityState === 'visible') {
        reloadPeserta();
        startPolling();
    } else {
        stopPolling();
    }
}

onMounted(() => {
    wilayah.loadProvinsi();
    startPolling();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', onVisibilityChange);
});

watch(() => wilayah.kodeDesa, (v: string) => {
    form.kode_wilayah_desa = v;
});

// ── Table columns ─────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'nomor_peserta',     label: 'No. Peserta' },
    { key: 'nama',              label: 'Nama Peserta' },
    { key: 'kafilah_nama',      label: 'Kafilah' },
    { key: 'cabang_nama',       label: 'Cabang' },
    { key: 'golongan_nama',     label: 'Golongan' },
    { key: 'jenis_kelamin',     label: 'JK' },
    { key: 'status',            label: 'Status' },
    { key: 'kode_wilayah_desa', label: 'Kode Wilayah', hidden: true },
];

// ── Row-level action guards ───────────────────────────────────────────────────

const rowCanView = (row: Record<string, unknown>) => {
    const r = row as unknown as PesertaRow;
    if (r.status === 'diajukan') return canVerifyReject.value;
    if (r.status === 'diverifikasi') return true;
    return false;
};

const rowCanEdit = (row: Record<string, unknown>) => {
    const r = row as unknown as PesertaRow;
    if (r.status === 'draft') return true;
    if (r.status === 'diverifikasi') return isSuperadmin.value || isAdmin.value;
    return false;
};

const rowCanDelete = (row: Record<string, unknown>) => {
    const r = row as unknown as PesertaRow;
    if (isUserRole.value) return false;
    if (isSuperadmin.value) return true;
    // admin: tidak bisa hapus yang sudah diverifikasi
    return r.status !== 'diverifikasi';
};

// ── Status helpers ────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; color: string; badgeClass: string }> = {
    draft:        { label: 'Draft',         color: 'secondary', badgeClass: 'text-bg-secondary' },
    diajukan:     { label: 'Diajukan',      color: 'info',      badgeClass: 'text-bg-info'      },
    diverifikasi: { label: 'Terverifikasi', color: 'success',   badgeClass: 'text-bg-success'   },
    ditolak:      { label: 'Ditolak',       color: 'danger',    badgeClass: 'text-bg-danger'     },
};

function statusLabel(value: string): string   { return statusConfig[value]?.label    ?? value; }
function statusBadgeClass(value: string): string { return statusConfig[value]?.badgeClass ?? 'text-bg-secondary'; }

// ── Status filter tabs ────────────────────────────────────────────────────────

const activeTab = ref<string>('');

const statusTabs = computed(() => [
    { value: '',             label: 'Semua',         color: 'secondary', count: props.peserta.length },
    { value: 'draft',        label: 'Draft',         color: 'secondary', count: props.peserta.filter(p => p.status === 'draft').length },
    { value: 'diajukan',     label: 'Diajukan',      color: 'info',      count: props.peserta.filter(p => p.status === 'diajukan').length },
    { value: 'diverifikasi', label: 'Terverifikasi', color: 'success',   count: props.peserta.filter(p => p.status === 'diverifikasi').length },
    { value: 'ditolak',      label: 'Ditolak',       color: 'danger',    count: props.peserta.filter(p => p.status === 'ditolak').length },
]);

const filteredPeserta = computed(() =>
    activeTab.value ? props.peserta.filter(p => p.status === activeTab.value) : props.peserta
);

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal([
    'pesertaModal',
    'ajukanEditModal',
    'ajukanHapusModal',
    'verifyPesertaModal',
    'rejectPesertaModal',
    'deletePesertaModal',
    'bulkDeletePesertaModal',
    'detailPesertaModal',
]);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<PesertaRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Options ───────────────────────────────────────────────────────────────────

const kafilahOptions = computed<Select2Option[]>(() =>
    props.kafilahs.map(k => ({ value: k.id, label: k.nama_kabupaten }))
);

const cabangOptions = computed(() =>
    props.cabangs.map(c => ({ value: c.id, label: c.nama }))
);

const filteredGolonganOptions = computed(() =>
    props.golongans
        .filter(g => String(g.cabang_id) === String(form.cabang_id))
        .map(g => ({ value: g.id, label: g.nama }))
);

const jkOptions = [
    { label: 'Laki-Laki (Putra)', value: 'L' },
    { label: 'Perempuan (Putri)', value: 'P' },
];

const berkasJenisOptions = [
    { label: 'Akta Lahir',          value: 'akte'             },
    { label: 'KTP / Kartu Pelajar', value: 'ktp'              },
    { label: 'Kartu Keluarga',      value: 'kk'               },
    { label: 'Surat Keterangan',    value: 'surat_keterangan' },
    { label: 'Lainnya',             value: 'lainnya'          },
];

function berkasLabel(jenis: string): string { return berkasJenisOptions.find(o => o.value === jenis)?.label ?? jenis; }

function fileIcon(mime: string | null): string {
    if (!mime) return 'ti-file';
    if (mime.includes('pdf'))   return 'ti-file-type-pdf';
    if (mime.includes('image')) return 'ti-photo';
    if (mime.includes('word') || mime.includes('document')) return 'ti-file-type-doc';
    return 'ti-file';
}

function formatSize(bytes: number): string {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// ── Create / Edit form ────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const editingRow = computed<PesertaRow | null>(() =>
    isEditing.value && editingId.value !== null
        ? props.peserta.find(p => p.id === editingId.value) ?? null
        : null
);

const form = useForm<PesertaForm>({
    kafilah_id:        '',
    cabang_id:         '',
    golongan_id:       '',
    nama:              '',
    nik:               '',
    jenis_kelamin:     '',
    tempat_lahir:      '',
    tgl_lahir:         '',
    alamat:            '',
    kode_wilayah_desa: '',
});

function resetForm(): void {
    form.kafilah_id        = '';
    form.cabang_id         = '';
    form.golongan_id       = '';
    form.nama              = '';
    form.nik               = '';
    form.jenis_kelamin     = '';
    form.tempat_lahir      = '';
    form.tgl_lahir         = '';
    form.alamat            = '';
    form.kode_wilayah_desa = '';
    form.clearErrors();
    wilayah.reset();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('pesertaModal');
}

function openEdit(row: Record<string, unknown>): void {
    const p = row as unknown as PesertaRow;
    isEditing.value        = true;
    editingId.value        = p.id;
    form.kafilah_id        = p.kafilah_id;
    form.cabang_id         = p.cabang_id;
    form.golongan_id       = p.golongan_id;
    form.nama              = p.nama;
    form.nik               = p.nik ?? '';
    form.jenis_kelamin     = p.jenis_kelamin;
    form.tempat_lahir      = p.tempat_lahir ?? '';
    form.tgl_lahir         = p.tgl_lahir ?? '';
    form.alamat            = p.alamat ?? '';
    form.kode_wilayah_desa = p.kode_wilayah_desa ?? '';
    form.clearErrors();
    wilayah.reset();
    if (p.kode_wilayah_desa) {
        wilayah.prefillFromKodeDesa(p.kode_wilayah_desa);
    }
    show('pesertaModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('pesertaModal') };
    if (isEditing.value && editingId.value) {
        form.put(`/mtq/peserta/${editingId.value}`, opts);
    } else {
        form.post('/mtq/peserta', { ...opts, onSuccess: () => { hide('pesertaModal'); resetForm(); } });
    }
}

// ── Foto (pas foto) upload ────────────────────────────────────────────────────

const fotoForm     = useForm<{ foto: File | null }>({ foto: null });
const fotoInputRef = ref<HTMLInputElement | null>(null);
const fotoError    = ref<string>('');

const FOTO_MAX_BYTES    = 2 * 1024 * 1024;
const FOTO_ALLOWED_EXT  = ['jpg', 'jpeg', 'png'];

function onFotoChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file  = input.files?.[0] ?? null;
    fotoError.value = '';
    fotoForm.foto   = null;

    if (!file) return;

    const ext = file.name.split('.').pop()?.toLowerCase() ?? '';
    if (!FOTO_ALLOWED_EXT.includes(ext)) {
        fotoError.value = `Format tidak didukung (.${ext}). Gunakan JPG atau PNG.`;
        input.value = '';
        return;
    }

    if (file.size > FOTO_MAX_BYTES) {
        fotoError.value = `Ukuran terlalu besar (${formatSize(file.size)}). Maksimal 2 MB.`;
        input.value = '';
        return;
    }

    fotoForm.foto = file;
}

function uploadFoto(): void {
    if (!editingId.value) return;
    fotoError.value = '';
    fotoForm.post(`/mtq/peserta/${editingId.value}/foto`, {
        preserveScroll: true,
        onSuccess: () => {
            fotoForm.reset();
            if (fotoInputRef.value) fotoInputRef.value.value = '';
        },
        onError: (errors) => {
            fotoError.value = errors.foto ?? 'Gagal mengupload foto.';
        },
    });
}

// ── Berkas upload ─────────────────────────────────────────────────────────────

const berkasForm   = useForm<{ jenis: string; file: File | null }>({ jenis: '', file: null });
const fileInputRef = ref<HTMLInputElement | null>(null);
const berkasError  = ref<string>('');

const BERKAS_MAX_BYTES  = 5 * 1024 * 1024;
const BERKAS_ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

function onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file  = input.files?.[0] ?? null;
    berkasError.value  = '';
    berkasForm.file    = null;

    if (!file) return;

    const ext = file.name.split('.').pop()?.toLowerCase() ?? '';
    if (!BERKAS_ALLOWED_EXT.includes(ext)) {
        berkasError.value = `Format file tidak didukung (.${ext}). Gunakan: PDF, JPG, PNG, DOC, atau DOCX.`;
        input.value = '';
        return;
    }

    if (file.size > BERKAS_MAX_BYTES) {
        berkasError.value = `Ukuran file terlalu besar (${formatSize(file.size)}). Maksimal yang diizinkan adalah 5 MB.`;
        input.value = '';
        return;
    }

    berkasForm.file = file;
}

function uploadBerkas(): void {
    if (!editingId.value) return;
    berkasError.value = '';
    berkasForm.post(`/mtq/peserta/${editingId.value}/berkas`, {
        preserveScroll: true,
        onSuccess: () => {
            berkasForm.reset();
            if (fileInputRef.value) fileInputRef.value.value = '';
        },
    });
}

function deleteBerkas(berkasId: number): void {
    router.delete(`/mtq/berkas/${berkasId}`, { preserveScroll: true });
}

// ── Submit for review ─────────────────────────────────────────────────────────

const submitProcessing = ref<number | null>(null);

function submitPeserta(row: PesertaRow): void {
    submitProcessing.value = row.id;
    router.post(`/mtq/peserta/${row.id}/submit`, {}, {
        preserveScroll: true,
        onFinish: () => { submitProcessing.value = null; },
    });
}

// ── Ajukan Edit ───────────────────────────────────────────────────────────────

const ajukanEditTarget = ref<PesertaRow | null>(null);
const ajukanEditForm   = useForm<{ pesan: string }>({ pesan: '' });

function openAjukanEdit(row: PesertaRow): void {
    ajukanEditTarget.value  = row;
    ajukanEditForm.pesan    = '';
    ajukanEditForm.clearErrors();
    show('ajukanEditModal');
}

function doAjukanEdit(): void {
    if (!ajukanEditTarget.value) return;
    ajukanEditForm.post(`/mtq/peserta/${ajukanEditTarget.value.id}/pengajuan-edit`, {
        preserveScroll: true,
        onSuccess: () => hide('ajukanEditModal'),
    });
}

// ── Ajukan Hapus (admin → superadmin) ────────────────────────────────────────

const ajukanHapusTarget = ref<PesertaRow | null>(null);
const ajukanHapusForm   = useForm<{ pesan: string }>({ pesan: '' });

function openAjukanHapus(row: PesertaRow): void {
    ajukanHapusTarget.value = row;
    ajukanHapusForm.pesan   = '';
    ajukanHapusForm.clearErrors();
    show('ajukanHapusModal');
}

function doAjukanHapus(): void {
    if (!ajukanHapusTarget.value) return;
    ajukanHapusForm.post(`/mtq/peserta/${ajukanHapusTarget.value.id}/pengajuan-hapus`, {
        preserveScroll: true,
        onSuccess: () => hide('ajukanHapusModal'),
    });
}

// ── Detail ────────────────────────────────────────────────────────────────────

const detailTarget = ref<PesertaRow | null>(null);

function openDetail(row: Record<string, unknown> | PesertaRow): void {
    detailTarget.value = row as unknown as PesertaRow;
    show('detailPesertaModal');
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function openVerifyFromDetail(): void {
    hide('detailPesertaModal');
    if (detailTarget.value) openVerify(detailTarget.value);
}

function openRejectFromDetail(): void {
    hide('detailPesertaModal');
    if (detailTarget.value) openReject(detailTarget.value);
}

// ── Verify ────────────────────────────────────────────────────────────────────

const verifyTarget = ref<PesertaRow | null>(null);
const verifyForm   = useForm<{ nomor_peserta: string }>({ nomor_peserta: '' });

function openVerify(row: PesertaRow): void {
    verifyTarget.value       = row;
    verifyForm.nomor_peserta = '';
    verifyForm.clearErrors();
    show('verifyPesertaModal');
}

function doVerify(): void {
    if (!verifyTarget.value) return;
    verifyForm.post(`/mtq/peserta/${verifyTarget.value.id}/verify`, {
        preserveScroll: true,
        onSuccess: () => hide('verifyPesertaModal'),
    });
}

// ── Reject ────────────────────────────────────────────────────────────────────

const rejectTarget = ref<PesertaRow | null>(null);
const rejectForm   = useForm<{ catatan_verifikasi: string }>({ catatan_verifikasi: '' });

function openReject(row: PesertaRow): void {
    rejectTarget.value            = row;
    rejectForm.catatan_verifikasi = '';
    rejectForm.clearErrors();
    show('rejectPesertaModal');
}

function doReject(): void {
    if (!rejectTarget.value) return;
    rejectForm.post(`/mtq/peserta/${rejectTarget.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => hide('rejectPesertaModal'),
    });
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<PesertaRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as PesertaRow;
    show('deletePesertaModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/peserta/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deletePesertaModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/peserta/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeletePesertaModal');
        },
    });
}
</script>

<style lang="scss" scoped>
.app-action-btn {
    width: 30px;
    height: 30px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px !important;
    flex-shrink: 0;
}

.detail-info-table th,
.detail-info-table td {
    padding-top: 0.3rem;
    padding-bottom: 0.3rem;
    vertical-align: top;
}

.detail-berkas-item {
    transition: background 0.15s;
    &:hover {
        background: var(--bs-tertiary-bg);
    }
}
</style>
