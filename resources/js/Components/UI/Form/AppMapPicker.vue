<template>
    <div>
        <div v-if="label" class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label mb-0">
                {{ label }}
                <span v-if="required" class="text-danger ms-1">*</span>
            </label>
            <button
                v-if="hasCoords"
                type="button"
                class="btn btn-link btn-sm p-0 text-danger text-decoration-none"
                @click="clearLocation"
            >
                <i class="ti ti-map-pin-off me-1"></i>Hapus lokasi
            </button>
        </div>

        <div
            ref="mapContainer"
            class="app-map-container rounded-3"
            :class="error ? 'border border-danger' : 'border'"
        ></div>

        <div class="app-map-coords mt-1 d-flex align-items-center gap-1">
            <template v-if="hasCoords">
                <i class="ti ti-map-pin text-primary" style="font-size:0.8rem"></i>
                <span class="font-monospace">{{ displayLat }}, {{ displayLng }}</span>
            </template>
            <span v-else class="fst-italic">Klik peta untuk menentukan lokasi</span>
        </div>

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';
import 'leaflet-geosearch/dist/geosearch.css';

// ── Props ─────────────────────────────────────────────────────────────────────

interface Props {
    lat?: number | string | null;
    lng?: number | string | null;
    label?: string;
    hint?: string;
    error?: string;
    required?: boolean;
    zoom?: number;
    defaultLat?: number;
    defaultLng?: number;
}

const props = withDefaults(defineProps<Props>(), {
    zoom: 14,
    defaultLat: 3.8634,  // Blangpidie, Aceh Barat Daya
    defaultLng: 96.7234,
    required: false,
});

const emit = defineEmits<{
    'update:lat': [value: number | null];
    'update:lng': [value: number | null];
}>();

// ── Refs ──────────────────────────────────────────────────────────────────────

const mapContainer = ref<HTMLElement | null>(null);

let map: L.Map | null = null;
let marker: L.Marker | null = null;
let resizeObserver: ResizeObserver | null = null;

// ── Pin icon (inline SVG — avoids Vite asset-URL issues) ──────────────────────

const pinIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="38" height="38">
        <path fill="#2563eb" stroke="white" stroke-width="0.5"
            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
        <circle fill="white" cx="12" cy="9" r="2.8"/>
    </svg>`,
    className: '',
    iconSize:   [38, 38],
    iconAnchor: [19, 38],
});

// ── Computed ──────────────────────────────────────────────────────────────────

const numLat = computed<number | null>(() => {
    const v = props.lat;
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return isNaN(n) ? null : n;
});

const numLng = computed<number | null>(() => {
    const v = props.lng;
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return isNaN(n) ? null : n;
});

const hasCoords  = computed(() => numLat.value !== null && numLng.value !== null);
const displayLat = computed(() => numLat.value?.toFixed(6) ?? '');
const displayLng = computed(() => numLng.value?.toFixed(6) ?? '');

// ── Map helpers ───────────────────────────────────────────────────────────────

function placeMarker(lat: number, lng: number): void {
    if (!map) return;
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { icon: pinIcon, draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const pos = marker!.getLatLng();
            emit('update:lat', pos.lat);
            emit('update:lng', pos.lng);
        });
    }
}

function removeMarker(): void {
    if (marker && map) {
        marker.remove();
        marker = null;
    }
}

function clearLocation(): void {
    removeMarker();
    emit('update:lat', null);
    emit('update:lng', null);
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
    if (!mapContainer.value) return;

    const centerLat = numLat.value ?? props.defaultLat;
    const centerLng = numLng.value ?? props.defaultLng;

    map = L.map(mapContainer.value, {
        center: [centerLat, centerLng],
        zoom: props.zoom,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    if (hasCoords.value) {
        placeMarker(numLat.value!, numLng.value!);
    }

    map.on('click', (e: L.LeafletMouseEvent) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
        emit('update:lat', e.latlng.lat);
        emit('update:lng', e.latlng.lng);
    });

    // ── GeoSearch control ─────────────────────────────────────────────────────
    const provider = new OpenStreetMapProvider({
        params: { countrycodes: 'id', addressdetails: 1 },
    });

    const searchControl = GeoSearchControl({
        provider,
        style: 'bar',
        showMarker: false,
        showPopup: false,
        autoClose: true,
        retainZoomLevel: false,
        animateZoom: true,
        keepResult: true,
        searchLabel: 'Cari lokasi...',
    });

    map.addControl(searchControl);

    map.on('geosearch/showlocation', (result: any) => {
        const lat = result.location.y as number;
        const lng = result.location.x as number;
        placeMarker(lat, lng);
        emit('update:lat', lat);
        emit('update:lng', lng);
    });

    // Auto-fix map size when inside a Bootstrap modal (opens with display:none initially)
    resizeObserver = new ResizeObserver(() => map?.invalidateSize());
    resizeObserver.observe(mapContainer.value);
});

// Sync external prop changes (e.g., switching from create → edit mode)
watch([() => props.lat, () => props.lng], ([newLat, newLng]) => {
    if (!map) return;
    const lat = (newLat !== null && newLat !== undefined && newLat !== '') ? Number(newLat) : null;
    const lng = (newLng !== null && newLng !== undefined && newLng !== '') ? Number(newLng) : null;
    if (lat !== null && lng !== null && !isNaN(lat) && !isNaN(lng)) {
        placeMarker(lat, lng);
        map.setView([lat, lng]);
    } else {
        removeMarker();
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    map?.remove();
    map = null;
});
</script>

<style lang="scss" scoped>
.app-map-container {
    height: 300px;
    width: 100%;
    z-index: 0;
}

.app-map-coords {
    font-size: 0.775rem;
    color: var(--bs-secondary-color);
    min-height: 1.3rem;
}
</style>

<style lang="scss">
// Override GeoSearch styles — unscoped so they reach Leaflet DOM elements
.leaflet-control-geosearch {
    width: 280px;

    form {
        border-radius: 8px !important;
        border: 1px solid var(--bs-border-color) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;

        input {
            font-size: 0.8125rem !important;
            color: var(--bs-body-color) !important;
            background-color: var(--bs-body-bg) !important;
            border-radius: 8px !important;

            &::placeholder { color: var(--bs-secondary-color) !important; }
        }

        .reset {
            color: var(--bs-secondary-color) !important;
            &:hover { color: var(--bs-danger) !important; }
        }
    }

    .results {
        border-radius: 0 0 8px 8px !important;
        border: 1px solid var(--bs-border-color) !important;
        border-top: none !important;
        background-color: var(--bs-body-bg) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;

        > * {
            font-size: 0.8125rem !important;
            color: var(--bs-body-color) !important;
            border-top: 1px solid var(--bs-border-color) !important;
            padding: 6px 10px !important;

            &:hover, &.active {
                background-color: var(--bs-primary-bg-subtle) !important;
                color: var(--bs-primary) !important;
            }
        }
    }
}
</style>
