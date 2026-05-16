<template>
    <!-- Trigger button -->
    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" @click="open">
        <i class="ti ti-radar fs-5"></i>
        <span>Scan Module</span>
    </button>

    <!-- Modal -->
    <div class="modal fade" id="permScannerModal" tabindex="-1" aria-labelledby="permScannerLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="permScannerLabel">
                        <i class="ti ti-radar text-primary fs-4"></i>
                        Permission Module Scanner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">

                    <!-- Terminal window -->
                    <div ref="terminalEl" class="terminal-window px-4 py-3"
                         style="background:#0d1117;min-height:200px;max-height:280px;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:0.8rem;color:#c9d1d9;overflow-y:auto;line-height:1.6">
                        <div v-if="terminalLines.length === 0" class="text-center py-5" style="color:#6e7681">
                            <i class="ti ti-terminal-2" style="font-size:2rem"></i>
                            <p class="mt-2 mb-0">Klik <strong style="color:#58a6ff">Mulai Scan</strong> untuk memindai modul</p>
                        </div>
                        <div v-for="(line, i) in terminalLines" :key="i" v-html="line"></div>
                        <span v-if="state === 'scanning'" class="cursor-blink" style="color:#58a6ff">█</span>
                    </div>

                    <!-- Results panel -->
                    <div v-if="state === 'results' || state === 'syncing' || state === 'done'" class="p-4">

                        <!-- Summary bar -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold">
                                    <i class="ti ti-package me-1 text-primary"></i>
                                    {{ newCount }} modul baru
                                </span>
                                <span class="text-muted small">·</span>
                                <span class="text-muted small">{{ totalNewPerms }} permission belum ada</span>
                                <span v-if="ignoredList.length" class="badge text-bg-secondary ms-1">
                                    {{ ignoredList.length }} diabaikan
                                </span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button v-if="ignoredList.length" class="btn btn-sm btn-outline-secondary"
                                        @click="showIgnored = !showIgnored">
                                    <i :class="showIgnored ? 'ti ti-eye-off' : 'ti ti-eye'" class="me-1"></i>
                                    {{ showIgnored ? 'Sembunyikan Diabaikan' : 'Tampilkan Diabaikan' }}
                                </button>
                                <button class="btn btn-sm btn-outline-primary" @click="selectAll">
                                    <i class="ti ti-checkbox me-1"></i>Pilih Semua
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" @click="deselectAll">
                                    <i class="ti ti-square me-1"></i>Hapus Pilihan
                                </button>
                            </div>
                        </div>

                        <!-- Module cards -->
                        <div class="row g-3">
                            <template v-for="mod in activeModules" :key="mod.name">
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border" :class="cardBorderClass(mod)">
                                        <div class="card-header py-2 d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <i :class="cardIcon(mod)" class="fs-5"></i>
                                                <span class="fw-semibold text-capitalize">{{ mod.name }}</span>
                                                <span class="badge" :class="cardBadge(mod)">{{ cardLabel(mod) }}</span>
                                            </div>
                                            <button v-if="!mod.all_exist" type="button"
                                                    class="btn btn-link p-0 text-muted"
                                                    title="Abaikan modul ini di scan berikutnya"
                                                    @click="ignoreModule(mod.name)">
                                                <i class="ti ti-eye-off fs-5"></i>
                                            </button>
                                        </div>
                                        <div class="card-body py-2 px-3">
                                            <div v-for="perm in mod.permissions" :key="perm.name"
                                                 class="d-flex align-items-center gap-2 py-1 border-bottom border-opacity-10">
                                                <!-- Already exists -->
                                                <i v-if="perm.exists"
                                                   class="ti ti-check text-success flex-shrink-0"
                                                   style="font-size:0.85rem;width:16px"></i>
                                                <!-- Checkbox for new -->
                                                <input v-else type="checkbox"
                                                       class="form-check-input m-0 flex-shrink-0"
                                                       :id="`ps-${perm.name}`"
                                                       v-model="selectedPerms"
                                                       :value="perm.name"
                                                       style="width:16px;height:16px" />
                                                <label :for="`ps-${perm.name}`"
                                                       class="small mb-0 flex-grow-1 text-truncate"
                                                       :class="perm.exists ? 'text-muted' : 'fw-medium'">
                                                    {{ perm.name }}
                                                </label>
                                                <span class="text-muted flex-shrink-0"
                                                      style="font-size:0.65rem;font-family:monospace">
                                                    {{ perm.method }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Ignored section -->
                            <template v-if="showIgnored && ignoredList.length">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase mt-1">
                                        <i class="ti ti-eye-off"></i> Diabaikan
                                    </div>
                                </div>
                                <div v-for="name in ignoredList" :key="name" class="col-md-6 col-xl-4">
                                    <div class="card border-dashed" style="opacity:0.6">
                                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                                            <span class="fw-semibold text-capitalize text-muted">
                                                <i class="ti ti-package me-1"></i>{{ name }}
                                            </span>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-primary"
                                                    @click="unignoreModule(name)">
                                                <i class="ti ti-eye me-1"></i>Pulihkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Idle placeholder -->
                    <div v-if="state === 'idle'" class="p-4 text-center text-muted">
                        <i class="ti ti-scan" style="font-size:2.5rem;opacity:0.3"></i>
                        <p class="mt-2 mb-0 small">Scanner akan membaca seluruh route dan mendeteksi permission<br>yang belum terdaftar di database.</p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <div class="me-auto small text-muted" v-if="state === 'results'">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ selectedPerms.length }} permission dipilih
                    </div>
                    <div class="me-auto small text-success fw-medium" v-if="state === 'done'">
                        <i class="ti ti-circle-check me-1"></i>
                        Sync selesai — reload halaman untuk melihat permission baru
                    </div>

                    <!-- Hard Reset: step 1 — trigger -->
                    <button v-if="!confirmingReset"
                            type="button" class="btn btn-link text-danger ms-0 me-auto px-0"
                            title="Hapus semua permission dari database lalu scan ulang dari nol"
                            :disabled="state === 'scanning' || state === 'syncing'"
                            @click="confirmingReset = true">
                        <i class="ti ti-refresh-alert me-1"></i>Hard Reset
                    </button>

                    <!-- Hard Reset: step 2 — confirm inline -->
                    <div v-else class="d-flex align-items-center gap-2 me-auto">
                        <span class="small text-danger fw-medium">
                            <i class="ti ti-alert-triangle me-1"></i>
                            Hapus <strong>semua permission</strong> dari DB? Role akan kehilangan akses.
                        </span>
                        <button type="button" class="btn btn-danger btn-sm" @click="hardReset">
                            <i class="ti ti-trash me-1"></i>Ya, Hapus Semua
                        </button>
                        <button type="button" class="btn btn-light btn-sm" @click="confirmingReset = false">
                            Batal
                        </button>
                    </div>

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>

                    <button v-if="state === 'idle' || state === 'done'"
                            type="button" class="btn btn-outline-primary" @click="startScan">
                        <i class="ti ti-radar me-1"></i>
                        {{ state === 'done' ? 'Scan Ulang' : 'Mulai Scan' }}
                    </button>

                    <button v-if="state === 'scanning'" type="button" class="btn btn-outline-secondary" disabled>
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Scanning...
                    </button>

                    <button v-if="state === 'results'"
                            type="button" class="btn btn-primary"
                            :disabled="!selectedPerms.length"
                            @click="syncPermissions">
                        <i class="ti ti-plus me-1"></i>
                        Tambahkan {{ selectedPerms.length || '' }} Permission
                    </button>

                    <button v-if="state === 'syncing'" type="button" class="btn btn-primary" disabled>
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Menyimpan...
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onBeforeUnmount } from 'vue';
import { Modal } from 'bootstrap';
import axios from 'axios';

const emit = defineEmits<{ synced: [] }>();

const IGNORED_KEY = 'perm_scanner_ignored_v1';

type ScanState = 'idle' | 'scanning' | 'results' | 'syncing' | 'done';

interface PermData {
    name: string;
    route: string | null;
    method: string;
    uri: string;
    exists: boolean;
}

interface ModuleData {
    name: string;
    permissions: PermData[];
    all_exist: boolean;
    some_exist: boolean;
}

const state           = ref<ScanState>('idle');
const terminalLines   = ref<string[]>([]);
const terminalEl      = ref<HTMLElement | null>(null);
const modules         = ref<ModuleData[]>([]);
const selectedPerms   = ref<string[]>([]);
const showIgnored     = ref(false);
const confirmingReset = ref(false);
const ignoredList     = ref<string[]>(JSON.parse(localStorage.getItem(IGNORED_KEY) ?? '[]'));

// Modules excluding ignored ones
const activeModules  = computed(() => modules.value.filter(m => !ignoredList.value.includes(m.name)));
const newCount       = computed(() => activeModules.value.filter(m => !m.all_exist).length);
const totalNewPerms  = computed(() =>
    activeModules.value.flatMap(m => m.permissions.filter(p => !p.exists)).length
);

// ── Terminal helpers ──────────────────────────────────────────────

const C = {
    green:  (t: string) => `<span style="color:#3fb950">${t}</span>`,
    yellow: (t: string) => `<span style="color:#e3b341">${t}</span>`,
    blue:   (t: string) => `<span style="color:#58a6ff">${t}</span>`,
    gray:   (t: string) => `<span style="color:#6e7681">${t}</span>`,
    red:    (t: string) => `<span style="color:#f85149">${t}</span>`,
    white:  (t: string) => `<span style="color:#e6edf3;font-weight:500">${t}</span>`,
    dim:    (t: string) => `<span style="color:#484f58">${t}</span>`,
};

async function addLine(html: string, delay = 55) {
    terminalLines.value.push(html);
    await nextTick();
    if (terminalEl.value) {
        terminalEl.value.scrollTop = terminalEl.value.scrollHeight;
    }
    if (delay > 0) await sleep(delay);
}

function sleep(ms: number) {
    return new Promise(r => setTimeout(r, ms));
}

// ── Modal ─────────────────────────────────────────────────────────

function getModal() {
    return Modal.getOrCreateInstance(document.getElementById('permScannerModal')!);
}

function open() {
    getModal().show();
}

defineExpose({ open });

// ── Scan ──────────────────────────────────────────────────────────

async function startScan() {
    state.value     = 'scanning';
    terminalLines.value = [];
    modules.value   = [];
    selectedPerms.value = [];

    const prompt = `${C.green('❯')} ${C.white('permission:scan --all-routes')}`;
    await addLine(prompt, 0);
    await addLine('', 0);
    await addLine(`${C.gray('┌')} ${C.blue('Permission Module Scanner')} ${C.gray('──────────────────────────')}`, 40);
    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Menghubungkan ke database...')}`, 80);
    await addLine(`${C.gray('│')}  ${C.green('✓')} Database terhubung`, 120);
    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Memuat semua route aplikasi...')}`, 80);

    try {
        const res  = await axios.post('/settings/permissions/scan');
        const data = res.data as { modules: ModuleData[]; total_routes: number; scanned_at: string };

        await addLine(`${C.gray('│')}  ${C.green('✓')} ${C.white(String(data.total_routes))} route ditemukan`, 60);
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('│')}  ${C.gray('Menganalisis middleware permission...')}`, 100);
        await addLine(`${C.gray('│')}`, 20);

        for (const mod of data.modules) {
            const newPerms  = mod.permissions.filter(p => !p.exists);
            const statusTxt = mod.all_exist
                ? C.green('✓ synced')
                : newPerms.length === mod.permissions.length
                    ? C.yellow('+ new')
                    : C.yellow('~ partial');

            const modLine = `${C.gray('│')}  ${C.gray('[')}${C.blue('mod')}${C.gray(']')}  ${C.white(mod.name.padEnd(18))}  ${statusTxt}  ${C.dim(mod.permissions.length + ' perm')}`;
            await addLine(modLine, 60);

            for (const perm of mod.permissions) {
                const icon = perm.exists ? C.green('✓') : C.yellow('+');
                const nameStr = perm.name.padEnd(32);
                const routeStr = C.dim(`${perm.method} ${perm.uri}`);
                await addLine(`${C.gray('│')}       ${icon}  ${nameStr} ${routeStr}`, 25);
            }
        }

        const newModCount = data.modules.filter(m => !m.all_exist).length;

        await addLine(`${C.gray('│')}`, 30);
        await addLine(`${C.gray('└')} ${C.green('Scan selesai')} ${C.gray('·')} ${C.white(data.scanned_at)}`, 60);
        await addLine('', 0);
        await addLine(
            newModCount > 0
                ? `  ${C.yellow('⚡')} ${C.white(String(newModCount) + ' modul')} memiliki permission baru yang belum ditambahkan.`
                : `  ${C.green('✓')} Semua modul sudah sinkron.`,
            0,
        );

        modules.value = data.modules;

        // Auto-select new permissions from non-ignored modules
        selectedPerms.value = data.modules
            .filter(m => !ignoredList.value.includes(m.name))
            .flatMap(m => m.permissions.filter(p => !p.exists).map(p => p.name));

        state.value = 'results';

    } catch (err: any) {
        const msg = err?.response?.status
            ? `HTTP ${err.response.status} — ${err.response?.data?.message ?? 'server error'}`
            : 'Tidak dapat terhubung ke server';
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('└')} ${C.red('✗ Scan gagal: ' + msg)}`, 0);
        state.value = 'idle';
    }
}

// ── Sync ──────────────────────────────────────────────────────────

async function syncPermissions() {
    if (! selectedPerms.value.length) return;
    state.value = 'syncing';

    await addLine('', 0);
    await addLine(`${C.green('❯')} ${C.white('Menyimpan ' + selectedPerms.value.length + ' permission...')}`, 0);

    try {
        const res     = await axios.post('/settings/permissions/sync', { permissions: selectedPerms.value });
        const created = res.data.created as string[];

        for (const p of created) {
            await addLine(`  ${C.green('+')} ${p}`, 35);
        }

        await addLine('', 0);
        await addLine(`  ${C.green('✓')} ${C.white(String(created.length) + ' permission')} berhasil ditambahkan.`, 0);

        state.value = 'done';
        emit('synced');

    } catch {
        await addLine(`  ${C.red('✗ Gagal — server error.')}`, 0);
        state.value = 'results';
    }
}

// ── Ignore ────────────────────────────────────────────────────────

function ignoreModule(name: string) {
    if (ignoredList.value.includes(name)) return;
    ignoredList.value.push(name);
    persist();
    // Remove this module's perms from selection
    const perms = modules.value.find(m => m.name === name)?.permissions.map(p => p.name) ?? [];
    selectedPerms.value = selectedPerms.value.filter(p => !perms.includes(p));
}

function unignoreModule(name: string) {
    ignoredList.value = ignoredList.value.filter(n => n !== name);
    persist();
}

function persist() {
    localStorage.setItem(IGNORED_KEY, JSON.stringify(ignoredList.value));
}

async function hardReset() {
    confirmingReset.value = false;
    state.value           = 'scanning';
    terminalLines.value   = [];
    modules.value         = [];
    selectedPerms.value   = [];
    showIgnored.value     = false;

    await addLine(`${C.green('❯')} ${C.white('permission:reset --force')}`, 0);
    await addLine('', 0);
    await addLine(`${C.gray('┌')} ${C.red('Hard Reset')} ${C.gray('──────────────────────────────────────')}`, 40);
    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Menghapus semua permission dari database...')}`, 80);

    try {
        const res = await axios.delete('/settings/permissions/reset');
        const deleted: number = res.data.deleted;

        await addLine(`${C.gray('│')}  ${C.red('✗')} ${C.white(String(deleted))} permission dihapus`, 80);
        await addLine(`${C.gray('│')}  ${C.red('✗')} Semua role kehilangan permission`, 60);
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('│')}  ${C.gray('Membersihkan daftar diabaikan...')}`, 60);

        ignoredList.value = [];
        localStorage.removeItem(IGNORED_KEY);

        await addLine(`${C.gray('│')}  ${C.green('✓')} Cache lokal dibersihkan`, 60);
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('└')} ${C.green('Reset selesai')} ${C.gray('· Klik')} ${C.blue('Mulai Scan')} ${C.gray('untuk scan ulang dari nol.')}`, 60);

        state.value = 'idle';
        emit('synced');
    } catch {
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('└')} ${C.red('✗ Reset gagal — server error.')}`, 0);
        state.value = 'idle';
    }
}

// ── Bulk select ───────────────────────────────────────────────────

function selectAll() {
    selectedPerms.value = activeModules.value
        .flatMap(m => m.permissions.filter(p => !p.exists).map(p => p.name));
}

function deselectAll() {
    selectedPerms.value = [];
}

// ── Card helpers ──────────────────────────────────────────────────

function cardBorderClass(mod: ModuleData) {
    if (mod.all_exist)  return 'border-success border-opacity-25';
    if (mod.some_exist) return 'border-warning border-opacity-50';
    return 'border-primary border-opacity-50';
}

function cardIcon(mod: ModuleData) {
    if (mod.all_exist)  return 'ti ti-circle-check text-success';
    if (mod.some_exist) return 'ti ti-circle-half-2 text-warning';
    return 'ti ti-circle-plus text-primary';
}

function cardBadge(mod: ModuleData) {
    if (mod.all_exist)  return 'text-bg-success';
    if (mod.some_exist) return 'text-bg-warning';
    return 'text-bg-primary';
}

function cardLabel(mod: ModuleData) {
    if (mod.all_exist)  return 'Synced';
    if (mod.some_exist) return 'Sebagian';
    return 'Baru';
}

// ── Cleanup ───────────────────────────────────────────────────────

onBeforeUnmount(() => {
    const el = document.getElementById('permScannerModal');
    if (el) Modal.getInstance(el)?.dispose();
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});
</script>

<style scoped>
.cursor-blink {
    animation: blink 1s step-end infinite;
}
@keyframes blink {
    50% { opacity: 0; }
}
.border-dashed {
    border-style: dashed !important;
}
</style>
