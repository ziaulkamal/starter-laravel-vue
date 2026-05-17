<template>
    <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
        <a class="nav-link position-relative" href="javascript:void(0)"
           data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-bell-ringing"></i>
            <span v-if="unreadCount > 0"
                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size: 0.6rem">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </a>

        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up p-0" style="min-width: 340px; max-width: 380px">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-semibold">Notifikasi</h6>
                <button v-if="items.length > 0"
                        class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                        @click.stop="markAllRead">
                    Tandai semua dibaca
                </button>
            </div>

            <!-- List -->
            <div style="max-height: 380px; overflow-y: auto">
                <template v-if="items.length > 0">
                    <div v-for="item in items" :key="item.id"
                         class="d-flex gap-3 px-3 py-3 border-bottom notification-item bg-light-subtle position-relative">

                        <!-- Tombol dismiss (×) — selalu ada di pojok kanan atas -->
                        <button
                            class="btn-dismiss position-absolute"
                            title="Sembunyikan notifikasi ini"
                            @click.stop="markRead(item)">
                            <i class="ti ti-x"></i>
                        </button>

                        <!-- ── new_registration ───────────────────────────── -->
                        <template v-if="item.data.type === 'new_registration'">
                            <div class="flex-shrink-0 mt-1">
                                <span class="badge text-bg-warning rounded-circle p-2">
                                    <i class="ti ti-user-plus fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">
                                    Pendaftaran Baru
                                    <span v-if="isOlderThan24h(item)"
                                          class="badge text-bg-warning ms-1" style="font-size: 0.55rem">
                                        Butuh Peninjauan
                                    </span>
                                    <span v-else
                                          class="badge text-bg-primary ms-1" style="font-size: 0.55rem">
                                        Baru
                                    </span>
                                </p>
                                <p class="mb-1 text-muted small lh-sm">
                                    <strong>{{ item.data.name }}</strong> ({{ item.data.email }})<br>
                                    <span v-if="item.data.phone">HP: +{{ item.data.phone }}<br></span>
                                    <span class="text-danger">IP: {{ item.data.ip_address }}</span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                                <a :href="item.data.url"
                                   class="btn btn-sm btn-outline-primary px-2 py-1 mt-2 d-inline-block" style="font-size: 0.7rem"
                                   @click="markRead(item)">
                                    Review
                                </a>
                            </div>
                        </template>

                        <!-- ── pengajuan_edit_dibuat ───────────────────────── -->
                        <template v-else-if="item.data.type === 'pengajuan_edit_dibuat'">
                            <div class="flex-shrink-0 mt-1">
                                <span class="badge text-bg-info rounded-circle p-2">
                                    <i class="ti ti-edit-circle fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">
                                    Pengajuan Edit
                                    <span class="badge text-bg-primary ms-1" style="font-size: 0.55rem">Baru</span>
                                </p>
                                <p class="mb-1 text-muted small lh-sm">
                                    <strong>{{ item.data.peserta_nama }}</strong> — {{ item.data.kafilah_nama }}<br>
                                    Pemohon: {{ item.data.requester_name }}<br>
                                    <span class="fst-italic">{{ item.data.pesan }}</span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                                <a :href="item.data.url"
                                   class="btn btn-sm btn-outline-info px-2 py-1 mt-2 d-inline-block" style="font-size: 0.7rem"
                                   @click="markRead(item)">
                                    Tinjau
                                </a>
                            </div>
                        </template>

                        <!-- ── pengajuan_edit_direspon ─────────────────────── -->
                        <template v-else-if="item.data.type === 'pengajuan_edit_direspon'">
                            <div class="flex-shrink-0 mt-1">
                                <span :class="['badge rounded-circle p-2', item.data.status === 'disetujui' ? 'text-bg-success' : 'text-bg-danger']">
                                    <i :class="['fs-5', item.data.status === 'disetujui' ? 'ti ti-check' : 'ti ti-x']"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">
                                    Respons Pengajuan Edit
                                    <span :class="['badge ms-1', item.data.status === 'disetujui' ? 'text-bg-success' : 'text-bg-danger']"
                                          style="font-size: 0.55rem">
                                        {{ item.data.status === 'disetujui' ? 'Disetujui' : 'Ditolak' }}
                                    </span>
                                </p>
                                <p class="mb-1 text-muted small lh-sm">
                                    Peserta: <strong>{{ item.data.peserta_nama }}</strong><br>
                                    <span v-if="item.data.catatan_admin" class="fst-italic">{{ item.data.catatan_admin }}</span>
                                    <span v-else class="fst-italic text-muted">Tidak ada catatan</span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                                <a :href="item.data.url"
                                   class="btn btn-sm btn-outline-secondary px-2 py-1 mt-2 d-inline-block" style="font-size: 0.7rem"
                                   @click="markRead(item)">
                                    Lihat
                                </a>
                            </div>
                        </template>

                        <!-- ── pengajuan_hapus_dibuat ─────────────────────── -->
                        <template v-else-if="item.data.type === 'pengajuan_hapus_dibuat'">
                            <div class="flex-shrink-0 mt-1">
                                <span class="badge text-bg-danger rounded-circle p-2">
                                    <i class="ti ti-trash fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">
                                    Pengajuan Hapus Peserta
                                    <span class="badge text-bg-danger ms-1" style="font-size: 0.55rem">Baru</span>
                                </p>
                                <p class="mb-1 text-muted small lh-sm">
                                    <strong>{{ item.data.peserta_nama }}</strong> — {{ item.data.kafilah_nama }}<br>
                                    Pemohon: {{ item.data.requester_name }}<br>
                                    <span class="fst-italic">{{ item.data.pesan }}</span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                                <a :href="item.data.url"
                                   class="btn btn-sm btn-outline-danger px-2 py-1 mt-2 d-inline-block" style="font-size: 0.7rem"
                                   @click="markRead(item)">
                                    Tinjau
                                </a>
                            </div>
                        </template>

                        <!-- ── pengajuan_hapus_direspon ────────────────────── -->
                        <template v-else-if="item.data.type === 'pengajuan_hapus_direspon'">
                            <div class="flex-shrink-0 mt-1">
                                <span :class="['badge rounded-circle p-2', item.data.status === 'disetujui' ? 'text-bg-danger' : 'text-bg-secondary']">
                                    <i :class="['fs-5', item.data.status === 'disetujui' ? 'ti ti-trash-off' : 'ti ti-x']"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">
                                    Respons Pengajuan Hapus
                                    <span :class="['badge ms-1', item.data.status === 'disetujui' ? 'text-bg-danger' : 'text-bg-secondary']"
                                          style="font-size: 0.55rem">
                                        {{ item.data.status === 'disetujui' ? 'Disetujui' : 'Ditolak' }}
                                    </span>
                                </p>
                                <p class="mb-1 text-muted small lh-sm">
                                    Peserta: <strong>{{ item.data.peserta_nama }}</strong><br>
                                    <span v-if="item.data.catatan_admin" class="fst-italic">{{ item.data.catatan_admin }}</span>
                                    <span v-else class="fst-italic text-muted">Tidak ada catatan</span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                                <a :href="item.data.url"
                                   class="btn btn-sm btn-outline-secondary px-2 py-1 mt-2 d-inline-block" style="font-size: 0.7rem"
                                   @click="markRead(item)">
                                    Lihat
                                </a>
                            </div>
                        </template>

                        <!-- ── fallback (unknown type) ────────────────────── -->
                        <template v-else>
                            <div class="flex-shrink-0 mt-1">
                                <span class="badge text-bg-secondary rounded-circle p-2">
                                    <i class="ti ti-bell fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden pe-4">
                                <p class="mb-1 fw-semibold text-truncate small">Notifikasi</p>
                                <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                            </div>
                        </template>
                    </div>
                </template>

                <p v-else class="text-muted text-center py-4 mb-0 small">Tidak ada notifikasi baru</p>

            </div>
        </div>
    </li>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const unreadCount = ref(0);
const items = ref([]);

let pollInterval  = null;
let audioCtx      = null;
let isFirstFetch  = true;
let prevCount     = 0;

function getAudioCtx() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
}

function playNotificationSound() {
    try {
        const ctx = getAudioCtx();
        if (ctx.state === 'suspended') ctx.resume();

        const now = ctx.currentTime;

        // Two-tone ping: 880 Hz → 1100 Hz
        [[now, 880], [now + 0.18, 1100]].forEach(([start, freq]) => {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type           = 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.25, start);
            gain.gain.exponentialRampToValueAtTime(0.001, start + 0.28);
            osc.start(start);
            osc.stop(start + 0.28);
        });
    } catch {
        // Web Audio API tidak tersedia — silent fallback
    }
}

async function fetchNotifications() {
    try {
        const res = await fetch('/api/internal/notifications', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();

        if (!isFirstFetch && data.unread_count > prevCount) {
            playNotificationSound();
        }

        isFirstFetch      = false;
        prevCount         = data.unread_count;
        unreadCount.value = data.unread_count;
        items.value       = data.items;
    } catch {
        // silent — network error during poll is non-critical
    }
}

function isOlderThan24h(item) {
    return (Date.now() / 1000 - item.created_at_ts) > 86400;
}

function xsrfToken() {
    const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
}

function postHeaders() {
    return {
        'X-XSRF-TOKEN': xsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
    };
}

async function markRead(item) {
    items.value = items.value.filter(i => i.id !== item.id);
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    prevCount = unreadCount.value;

    await fetch(`/api/internal/notifications/${item.id}/read`, {
        method: 'POST',
        headers: postHeaders(),
    });
}

async function markAllRead() {
    items.value = [];
    unreadCount.value = 0;
    prevCount = 0;

    await fetch('/api/internal/notifications/read-all', {
        method: 'POST',
        headers: postHeaders(),
    });
}

onMounted(() => {
    if (page.props.auth?.user) {
        fetchNotifications();
        pollInterval = setInterval(fetchNotifications, 30_000);
    }
});

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<style scoped>
.notification-item {
    transition: background 0.15s;
}
.notification-item:hover {
    background: var(--bs-tertiary-bg) !important;
}
.btn-dismiss {
    top: 6px;
    right: 8px;
    width: 20px;
    height: 20px;
    padding: 0;
    background: none;
    border: none;
    color: var(--bs-secondary-color);
    opacity: 0.4;
    font-size: 0.75rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.15s, background 0.15s;
}
.btn-dismiss:hover {
    opacity: 1;
    background: var(--bs-danger-bg-subtle);
    color: var(--bs-danger);
}
</style>
