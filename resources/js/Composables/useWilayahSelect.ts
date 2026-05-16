import { ref } from 'vue';
import type { Select2Option } from '@/Components/UI/Form/AppSelect2.vue';

export interface WilayahSelectState {
    kodeProvinsi:  string;
    kodeKabupaten: string;
    kodeKecamatan: string;
    kodeDesa:      string;

    optProvinsi:   Select2Option[];
    optKabupaten:  Select2Option[];
    optKecamatan:  Select2Option[];
    optDesa:       Select2Option[];

    loadingKabupaten: boolean;
    loadingKecamatan: boolean;
    loadingDesa:      boolean;

    loadProvinsi(): Promise<void>;
    onProvinsiChange(kode: string | number | null | undefined): void;
    onKabupatenChange(kode: string | number | null | undefined): void;
    onKecamatanChange(kode: string | number | null | undefined): void;
    prefillFromKodeDesa(savedKode: string): Promise<void>;
    reset(): void;
}

export function useWilayahSelect(): WilayahSelectState {
    const kodeProvinsi  = ref('');
    const kodeKabupaten = ref('');
    const kodeKecamatan = ref('');
    const kodeDesa      = ref('');

    const optProvinsi  = ref<Select2Option[]>([]);
    const optKabupaten = ref<Select2Option[]>([]);
    const optKecamatan = ref<Select2Option[]>([]);
    const optDesa      = ref<Select2Option[]>([]);

    const loadingKabupaten = ref(false);
    const loadingKecamatan = ref(false);
    const loadingDesa      = ref(false);

    async function fetchJson(url: string): Promise<Select2Option[]> {
        const res = await fetch(url, { credentials: 'same-origin' });
        return res.json();
    }

    async function loadProvinsi(): Promise<void> {
        optProvinsi.value = await fetchJson('/api/wilayah/provinsi');
    }

    async function loadKabupaten(provinsiKode: string): Promise<void> {
        loadingKabupaten.value = true;
        optKabupaten.value = await fetchJson(`/api/wilayah/kabupaten?kode=${encodeURIComponent(provinsiKode)}`);
        loadingKabupaten.value = false;
    }

    async function loadKecamatan(kabupatenKode: string): Promise<void> {
        loadingKecamatan.value = true;
        optKecamatan.value = await fetchJson(`/api/wilayah/kecamatan?kode=${encodeURIComponent(kabupatenKode)}`);
        loadingKecamatan.value = false;
    }

    async function loadDesa(kecamatanKode: string): Promise<void> {
        loadingDesa.value = true;
        optDesa.value = await fetchJson(`/api/wilayah/kelurahan?kode=${encodeURIComponent(kecamatanKode)}`);
        loadingDesa.value = false;
    }

    // Called via @update:model-value on Provinsi AppSelect2 (user interaction only)
    function onProvinsiChange(kode: string | number | null | undefined): void {
        const k = String(kode ?? '');
        kodeKabupaten.value = '';
        kodeKecamatan.value = '';
        kodeDesa.value      = '';
        optKabupaten.value  = [];
        optKecamatan.value  = [];
        optDesa.value       = [];
        if (k) loadKabupaten(k);
    }

    // Called via @update:model-value on Kabupaten AppSelect2 (user interaction only)
    function onKabupatenChange(kode: string | number | null | undefined): void {
        const k = String(kode ?? '');
        kodeKecamatan.value = '';
        kodeDesa.value      = '';
        optKecamatan.value  = [];
        optDesa.value       = [];
        if (k) loadKecamatan(k);
    }

    // Called via @update:model-value on Kecamatan AppSelect2 (user interaction only)
    function onKecamatanChange(kode: string | number | null | undefined): void {
        const k = String(kode ?? '');
        kodeDesa.value = '';
        optDesa.value  = [];
        if (k) loadDesa(k);
    }

    // Pre-fill all 4 levels from a stored kode desa (len 13: XX.XX.XX.XXXX)
    async function prefillFromKodeDesa(savedKode: string): Promise<void> {
        if (!savedKode || savedKode.length !== 13) {
            reset();
            return;
        }
        const prov = savedKode.slice(0, 2);
        const kab  = savedKode.slice(0, 5);
        const kec  = savedKode.slice(0, 8);

        // Set values immediately (AppSelect2 will show loading state until options arrive)
        kodeProvinsi.value  = prov;
        kodeKabupaten.value = kab;
        kodeKecamatan.value = kec;
        kodeDesa.value      = savedKode;

        // Load all child options in parallel
        await Promise.all([
            loadKabupaten(prov),
            loadKecamatan(kab),
            loadDesa(kec),
        ]);
    }

    function reset(): void {
        kodeProvinsi.value  = '';
        kodeKabupaten.value = '';
        kodeKecamatan.value = '';
        kodeDesa.value      = '';
        optKabupaten.value  = [];
        optKecamatan.value  = [];
        optDesa.value       = [];
    }

    return {
        kodeProvinsi,  kodeKabupaten, kodeKecamatan, kodeDesa,
        optProvinsi,   optKabupaten,  optKecamatan,  optDesa,
        loadingKabupaten, loadingKecamatan, loadingDesa,
        loadProvinsi,
        onProvinsiChange, onKabupatenChange, onKecamatanChange,
        prefillFromKodeDesa, reset,
    } as unknown as WilayahSelectState;
}
