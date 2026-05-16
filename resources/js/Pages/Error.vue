<template>
    <Head :title="`${status} — ${config.title}`" />

    <div
        class="min-vh-100 d-flex align-items-center justify-content-center py-5"
        style="background: radial-gradient(ellipse at 50% 50%, rgba(93,135,255,.10) 0%, transparent 70%)"
    >
        <div class="text-center px-4" style="max-width: 520px; width: 100%">

            <!-- Ghost number -->
            <div
                aria-hidden="true"
                class="fw-black text-primary select-none"
                style="font-size: clamp(6rem, 22vw, 11rem); line-height: 1; letter-spacing: -0.04em; opacity: .08"
            >{{ status }}</div>

            <!-- Icon -->
            <div class="mt-n4 mb-4">
                <i :class="['ti', config.icon]" style="font-size: 3.5rem" :style="{ color: config.color }"></i>
            </div>

            <!-- Text -->
            <h1 class="h3 fw-bold mb-2">{{ config.title }}</h1>
            <p class="text-muted mb-5" style="font-size: 1rem; line-height: 1.6">{{ config.description }}</p>

            <!-- Actions -->
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <Link href="/" class="btn btn-primary px-4">
                    <i class="ti ti-layout-dashboard me-1 fs-5"></i>Dashboard
                </Link>
                <button class="btn btn-outline-secondary px-4" @click="goBack">
                    <i class="ti ti-arrow-left me-1 fs-5"></i>Kembali
                </button>
            </div>

            <!-- Help text for 419 -->
            <div v-if="status === 419" class="mt-4">
                <button class="btn btn-sm btn-light" @click="reload">
                    <i class="ti ti-refresh me-1"></i>Muat Ulang Halaman
                </button>
            </div>

            <!-- Reset option for 500 -->
            <div v-if="status === 500" class="mt-4">
                <p class="text-muted small mb-2">Jika masalah berlanjut setelah restart server, coba setel ulang aplikasi.</p>
                <button class="btn btn-sm btn-outline-danger" @click="hardReset">
                    <i class="ti ti-rotate me-1"></i>Setel Ulang &amp; Masuk Ulang
                </button>
            </div>

            <!-- Subtle footer -->
            <p class="text-muted mt-5 small mb-0" style="opacity: .5">
                HTTP {{ status }} &middot; {{ appName }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{ status: number }>();

const appName = computed(() => (usePage().props as any).appName ?? 'App');

interface ErrorConfig {
    title: string;
    description: string;
    icon: string;
    color: string;
}

const configs: Record<number, ErrorConfig> = {
    403: {
        title: 'Akses Ditolak',
        description: 'Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika Anda merasa ini adalah kesalahan.',
        icon: 'ti-lock',
        color: 'var(--bs-warning)',
    },
    404: {
        title: 'Halaman Tidak Ditemukan',
        description: 'Halaman yang Anda cari tidak ada, sudah dipindahkan, atau salah ketik URL.',
        icon: 'ti-ghost-2',
        color: 'var(--bs-primary)',
    },
    419: {
        title: 'Sesi Kedaluwarsa',
        description: 'Token sesi Anda telah habis. Muat ulang halaman untuk melanjutkan.',
        icon: 'ti-clock-exclamation',
        color: 'var(--bs-info)',
    },
    429: {
        title: 'Terlalu Banyak Permintaan',
        description: 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.',
        icon: 'ti-alert-triangle',
        color: 'var(--bs-warning)',
    },
    500: {
        title: 'Kesalahan Server',
        description: 'Terjadi kesalahan pada server kami. Tim teknis sudah diberitahu. Silakan coba lagi dalam beberapa saat.',
        icon: 'ti-server-off',
        color: 'var(--bs-danger)',
    },
    503: {
        title: 'Sedang Dalam Pemeliharaan',
        description: 'Layanan sedang dalam pemeliharaan terjadwal. Silakan coba lagi beberapa saat lagi.',
        icon: 'ti-tools',
        color: 'var(--bs-secondary)',
    },
};

const fallback: ErrorConfig = {
    title: 'Terjadi Kesalahan',
    description: 'Sesuatu yang tidak terduga terjadi. Silakan coba kembali atau hubungi administrator.',
    icon: 'ti-circle-x',
    color: 'var(--bs-danger)',
};

const config = computed<ErrorConfig>(() => configs[props.status] ?? fallback);

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/');
    }
}

function reload() {
    window.location.reload();
}

function hardReset() {
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = '/reset';
}
</script>
