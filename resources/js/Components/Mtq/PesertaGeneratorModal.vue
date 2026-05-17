<template>
    <!-- Trigger -->
    <button
        type="button"
        class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1"
        @click="open"
    >
        <i class="ti ti-wand fs-5"></i>
        <span>Generator</span>
    </button>

    <!-- Modal -->
    <div
        class="modal fade"
        id="pesertaGeneratorModal"
        tabindex="-1"
        aria-labelledby="pesertaGeneratorLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="pesertaGeneratorLabel">
                        <i class="ti ti-wand text-warning fs-4"></i>
                        Generator Peserta Otomatis
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">

                    <!-- ── Form State ───────────────────────────────────────── -->
                    <div v-if="state === 'form'" class="p-4">
                        <p class="text-muted small mb-4">
                            <i class="ti ti-robot me-1"></i>
                            Peserta digenerate dengan data acak dan berstatus <strong>draft</strong>.
                            Pilih minimal 1 item dari setiap kategori.
                        </p>

                        <!-- Jumlah -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Jumlah Peserta</label>
                            <div class="d-flex align-items-center gap-3">
                                <input
                                    v-model.number="form.jumlah"
                                    type="range"
                                    min="1"
                                    max="500"
                                    class="form-range flex-grow-1"
                                />
                                <input
                                    v-model.number="form.jumlah"
                                    type="number"
                                    min="1"
                                    max="500"
                                    class="form-control text-center fw-bold"
                                    style="width: 80px"
                                />
                                <span class="text-muted small text-nowrap">/ 500</span>
                            </div>
                        </div>

                        <!-- 2x2 multiselect grid -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <MultiCheckList
                                    label="Kafilah"
                                    :options="kafilahOpts"
                                    v-model="form.kafilah_ids"
                                />
                            </div>
                            <div class="col-md-6">
                                <MultiCheckList
                                    label="Cabang Lomba"
                                    :options="cabangOpts"
                                    v-model="form.cabang_ids"
                                />
                            </div>
                            <div class="col-md-6">
                                <MultiCheckList
                                    label="Golongan"
                                    description="Difilter sesuai cabang yang dipilih"
                                    :options="filteredGolonganOpts"
                                    :empty-message="form.cabang_ids.length ? 'Tidak ada golongan' : 'Pilih cabang terlebih dahulu'"
                                    v-model="form.golongan_ids"
                                />
                            </div>
                            <div class="col-md-6">
                                <MultiCheckList
                                    label="Kriteria (Opsional)"
                                    description="Hanya gunakan cabang yang memiliki kriteria ini"
                                    :options="filteredKriteriaOpts"
                                    :empty-message="form.cabang_ids.length ? 'Tidak ada kriteria' : 'Pilih cabang terlebih dahulu'"
                                    v-model="form.kriteria_ids"
                                />
                            </div>
                        </div>

                        <!-- Validation error -->
                        <div v-if="formError" class="alert alert-danger mt-4 py-2 small mb-0">
                            <i class="ti ti-alert-circle me-1"></i>{{ formError }}
                        </div>
                    </div>

                    <!-- ── Terminal (generating + done + error) ─────────────── -->
                    <div
                        v-show="state !== 'form'"
                        ref="terminalEl"
                        class="px-4 py-3"
                        style="background:#0d1117;min-height:340px;max-height:440px;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:0.8rem;color:#c9d1d9;overflow-y:auto;line-height:1.65"
                    >
                        <div v-if="terminalLines.length === 0" class="text-center py-5" style="color:#6e7681">
                            <i class="ti ti-terminal-2" style="font-size:2rem"></i>
                        </div>
                        <div v-for="(line, i) in terminalLines" :key="i" v-html="line"></div>
                        <span v-if="state === 'generating'" class="cursor-blink" style="color:#58a6ff">█</span>
                    </div>

                    <!-- ── Summary (done) ──────────────────────────────────── -->
                    <div v-if="state === 'done' && result" class="p-4 border-top">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <div class="card text-center border-success bg-success-subtle h-100 justify-content-center">
                                    <div class="card-body py-3">
                                        <div class="display-6 fw-bold text-success">{{ result.generated }}</div>
                                        <div class="small text-muted mt-1">Peserta</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card h-100">
                                    <div class="card-header py-2 d-flex align-items-center gap-2">
                                        <i class="ti ti-flag text-primary fs-5"></i>
                                        <span class="small fw-semibold">Per Kafilah</span>
                                    </div>
                                    <div class="card-body py-0 px-0 overflow-auto" style="max-height:140px">
                                        <div
                                            v-for="(count, name) in result.by_kafilah"
                                            :key="name"
                                            class="d-flex justify-content-between align-items-center small px-3 py-1 border-bottom"
                                        >
                                            <span class="text-truncate me-2">{{ name }}</span>
                                            <span class="badge text-bg-primary rounded-pill flex-shrink-0">{{ count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card h-100">
                                    <div class="card-header py-2 d-flex align-items-center gap-2">
                                        <i class="ti ti-tournament text-info fs-5"></i>
                                        <span class="small fw-semibold">Per Cabang</span>
                                    </div>
                                    <div class="card-body py-0 px-0 overflow-auto" style="max-height:140px">
                                        <div
                                            v-for="(count, name) in result.by_cabang"
                                            :key="name"
                                            class="d-flex justify-content-between align-items-center small px-3 py-1 border-bottom"
                                        >
                                            <span class="text-truncate me-2">{{ name }}</span>
                                            <span class="badge text-bg-info rounded-pill flex-shrink-0">{{ count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- end modal-body -->

                <!-- ── Footer ─────────────────────────────────────────────── -->
                <div class="modal-footer">
                    <div v-if="state === 'done'" class="me-auto small text-success fw-medium">
                        <i class="ti ti-circle-check me-1"></i>
                        {{ result?.generated }} peserta berhasil digenerate — halaman akan diperbarui
                    </div>
                    <div v-if="state === 'error'" class="me-auto small text-danger fw-medium">
                        <i class="ti ti-alert-circle me-1"></i>Generate gagal
                    </div>

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>

                    <!-- Form: generate button -->
                    <button
                        v-if="state === 'form'"
                        type="button"
                        class="btn btn-warning"
                        :disabled="!canGenerate"
                        @click="startGenerate"
                    >
                        <i class="ti ti-wand me-1"></i>
                        Generate {{ form.jumlah }} Peserta
                    </button>

                    <!-- Done: generate again -->
                    <button v-if="state === 'done'" type="button" class="btn btn-outline-warning" @click="resetToForm">
                        <i class="ti ti-refresh me-1"></i>Generate Lagi
                    </button>

                    <!-- Error: retry -->
                    <button v-if="state === 'error'" type="button" class="btn btn-outline-warning" @click="startGenerate">
                        <i class="ti ti-refresh me-1"></i>Coba Lagi
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import axios from 'axios';
import MultiCheckList from './MultiCheckList.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface KafilahItem  { id: number; nama_kabupaten: string; }
interface CabangItem   { id: number; nama: string; }
interface GolonganItem { id: number; cabang_id: number; nama: string; jenis_kelamin: string; }
interface KriteriaItem { id: number; cabang_id: number; nama: string; }

interface GeneratedItem {
    nama:          string;
    jenis_kelamin: string;
    kafilah_nama:  string;
    cabang_nama:   string;
    golongan_nama: string;
}

interface GenerateResult {
    generated:  number;
    items:      GeneratedItem[];
    by_kafilah: Record<string, number>;
    by_cabang:  Record<string, number>;
}

type State = 'form' | 'generating' | 'done' | 'error';

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kafilahs:  KafilahItem[];
    cabangs:   CabangItem[];
    golongans: GolonganItem[];
    kriteria:  KriteriaItem[];
}>();

// ── State ─────────────────────────────────────────────────────────────────────

const state       = ref<State>('form');
const formError   = ref<string>('');
const result      = ref<GenerateResult | null>(null);
const terminalEl  = ref<HTMLElement | null>(null);
const terminalLines = ref<string[]>([]);

// ── Form ──────────────────────────────────────────────────────────────────────

const form = ref({
    jumlah:       50,
    kafilah_ids:  [] as number[],
    cabang_ids:   [] as number[],
    golongan_ids: [] as number[],
    kriteria_ids: [] as number[],
});

// ── Computed options ──────────────────────────────────────────────────────────

const kafilahOpts = computed(() =>
    props.kafilahs.map(k => ({ value: k.id, label: k.nama_kabupaten }))
);

const cabangOpts = computed(() =>
    props.cabangs.map(c => ({ value: c.id, label: c.nama }))
);

const filteredGolonganOpts = computed(() =>
    props.golongans
        .filter(g => form.value.cabang_ids.includes(g.cabang_id))
        .map(g => ({ value: g.id, label: g.nama }))
);

const filteredKriteriaOpts = computed(() =>
    props.kriteria
        .filter(k => form.value.cabang_ids.includes(k.cabang_id))
        .map(k => ({ value: k.id, label: k.nama }))
);

// Remove deselected golongans/kriteria when cabangs change
watch(() => form.value.cabang_ids, () => {
    const validGolonganIds = new Set(filteredGolonganOpts.value.map(o => o.value));
    form.value.golongan_ids = form.value.golongan_ids.filter(id => validGolonganIds.has(id));

    const validKriteriaIds = new Set(filteredKriteriaOpts.value.map(o => o.value));
    form.value.kriteria_ids = form.value.kriteria_ids.filter(id => validKriteriaIds.has(id));
});

const canGenerate = computed(() =>
    form.value.jumlah >= 1 &&
    form.value.kafilah_ids.length > 0 &&
    form.value.cabang_ids.length > 0 &&
    form.value.golongan_ids.length > 0
);

// ── Terminal helpers ──────────────────────────────────────────────────────────

const C = {
    green:  (t: string) => `<span style="color:#3fb950">${t}</span>`,
    yellow: (t: string) => `<span style="color:#e3b341">${t}</span>`,
    blue:   (t: string) => `<span style="color:#58a6ff">${t}</span>`,
    cyan:   (t: string) => `<span style="color:#79c0ff">${t}</span>`,
    gray:   (t: string) => `<span style="color:#6e7681">${t}</span>`,
    red:    (t: string) => `<span style="color:#f85149">${t}</span>`,
    white:  (t: string) => `<span style="color:#e6edf3;font-weight:500">${t}</span>`,
    dim:    (t: string) => `<span style="color:#484f58">${t}</span>`,
    pink:   (t: string) => `<span style="color:#f0a8d0">${t}</span>`,
};

function sleep(ms: number): Promise<void> {
    return new Promise(r => setTimeout(r, ms));
}

async function addLine(html: string, delay = 50): Promise<void> {
    terminalLines.value.push(html);
    await nextTick();
    if (terminalEl.value) {
        terminalEl.value.scrollTop = terminalEl.value.scrollHeight;
    }
    if (delay > 0) await sleep(delay);
}

// ── Modal ─────────────────────────────────────────────────────────────────────

function getModal(): Modal {
    return Modal.getOrCreateInstance(document.getElementById('pesertaGeneratorModal')!);
}

function open(): void {
    getModal().show();
}

// ── Generate ──────────────────────────────────────────────────────────────────

async function startGenerate(): Promise<void> {
    formError.value     = '';
    state.value         = 'generating';
    terminalLines.value = [];
    result.value        = null;
    const startTime     = Date.now();

    const { jumlah, kafilah_ids, cabang_ids, golongan_ids, kriteria_ids } = form.value;

    const prompt = `${C.green('❯')} ${C.white('peserta:generate')} ${C.dim(`--count=${jumlah}`)}`;
    await addLine(prompt, 0);
    await addLine('', 0);
    await addLine(`${C.gray('┌')} ${C.yellow('Peserta Generator')} ${C.gray('──────────────────────────────────')}`, 40);
    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Menghubungkan ke database...')}`, 80);
    await addLine(`${C.gray('│')}  ${C.green('✓')} Database terhubung`, 100);
    await addLine(`${C.gray('│')}`, 20);

    // Summary config
    await addLine(`${C.gray('│')}  ${C.blue('[')}kafilah${C.blue(']')}  ${C.white(String(kafilah_ids.length))} dipilih`, 50);
    await addLine(`${C.gray('│')}  ${C.blue('[')}cabang ${C.blue(']')}  ${C.white(String(cabang_ids.length))} dipilih`, 50);
    await addLine(`${C.gray('│')}  ${C.blue('[')}golonga${C.blue(']')}  ${C.white(String(golongan_ids.length))} dipilih`, 50);
    if (kriteria_ids.length) {
        await addLine(`${C.gray('│')}  ${C.blue('[')}kriteri${C.blue(']')}  ${C.white(String(kriteria_ids.length))} dipilih ${C.dim('(filter cabang aktif)')}`, 50);
    }

    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Memvalidasi kombinasi golongan ↔ cabang...')}`, 80);

    // API call
    let data: GenerateResult;
    try {
        const res = await axios.post('/api/internal/mtq/peserta/generate', {
            jumlah,
            kafilah_ids,
            cabang_ids,
            golongan_ids,
            kriteria_ids: kriteria_ids.length ? kriteria_ids : undefined,
        });
        data = res.data as GenerateResult;
    } catch (err: any) {
        const msg = err?.response?.data?.error
            ?? err?.response?.data?.message
            ?? `HTTP ${err?.response?.status ?? '?'} — server error`;
        await addLine(`${C.gray('│')}`, 20);
        await addLine(`${C.gray('└')} ${C.red('✗ Generate gagal: ' + msg)}`, 0);
        state.value = 'error';
        return;
    }

    await addLine(`${C.gray('│')}  ${C.green('✓')} Kombinasi valid — ${C.white(String(data.generated))} peserta akan digenerate`, 60);
    await addLine(`${C.gray('│')}`, 20);
    await addLine(`${C.gray('│')}  ${C.gray('Menyimpan ke database...')}`, 60);
    await addLine(`${C.gray('│')}`, 20);

    // Animate items (max 40)
    const showItems = data.items.slice(0, 40);
    for (const item of showItems) {
        const jkColor = item.jenis_kelamin === 'L' ? C.cyan('♂') : C.pink('♀');
        const nama     = item.nama.padEnd(24).substring(0, 24);
        const kafilah  = item.kafilah_nama.replace('Kabupaten ', 'Kab. ').replace('Kota ', '').substring(0, 16).padEnd(16);
        const cabang   = item.cabang_nama.substring(0, 14).padEnd(14);
        const golongan = item.golongan_nama;
        const line = `${C.gray('│')}  ${C.green('[gen]')} ${jkColor}  ${C.white(nama)} ${C.yellow(kafilah)} ${C.blue(cabang)} ${C.dim(golongan)}`;
        await addLine(line, 18);
    }

    if (data.generated > showItems.length) {
        await addLine(`${C.gray('│')}`, 0);
        await addLine(
            `${C.gray('│')}  ${C.dim('...')} dan ${C.white(String(data.generated - showItems.length))} peserta lainnya`,
            40,
        );
    }

    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
    await addLine(`${C.gray('│')}`, 40);
    await addLine(`${C.gray('│')}  ${C.green('✓')} ${C.white(String(data.generated) + ' peserta')} berhasil disimpan ke database`, 0);
    await addLine(`${C.gray('└')} ${C.green('Selesai')} ${C.gray('·')} ${C.dim(elapsed + 's')}`, 60);

    result.value = data;
    state.value  = 'done';

    // Reload Inertia page so table reflects new data
    router.reload({ only: ['peserta'] });
}

function resetToForm(): void {
    state.value         = 'form';
    terminalLines.value = [];
    result.value        = null;
    formError.value     = '';
}

// ── Cleanup ───────────────────────────────────────────────────────────────────

onBeforeUnmount(() => {
    const el = document.getElementById('pesertaGeneratorModal');
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
</style>
