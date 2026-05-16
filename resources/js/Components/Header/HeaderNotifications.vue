<template>
    <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
        <a class="nav-link position-relative" href="javascript:void(0)"
           data-bs-toggle="dropdown" aria-expanded="false"
           @click="onOpen">
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
                <button v-if="unreadCount > 0"
                        class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                        @click.stop="markAllRead">
                    Tandai semua dibaca
                </button>
            </div>

            <!-- List -->
            <div style="max-height: 380px; overflow-y: auto">
                <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-muted"></div>
                </div>

                <template v-else-if="items.length > 0">
                    <div v-for="item in items" :key="item.id"
                         :class="['d-flex gap-3 px-4 py-3 border-bottom notification-item', { 'bg-light-subtle': !item.read }]"
                         @click="markRead(item)">

                        <div class="flex-shrink-0 mt-1">
                            <span class="badge text-bg-warning rounded-circle p-2">
                                <i class="ti ti-user-plus fs-5"></i>
                            </span>
                        </div>

                        <div class="flex-grow-1 overflow-hidden">
                            <p class="mb-1 fw-semibold text-truncate small">
                                Pendaftaran Baru
                                <span v-if="!item.read" class="badge text-bg-primary ms-1" style="font-size: 0.55rem">Baru</span>
                            </p>
                            <p class="mb-1 text-muted small lh-sm">
                                <strong>{{ item.data.name }}</strong> ({{ item.data.email }})<br>
                                <span v-if="item.data.phone">HP: +{{ item.data.phone }}<br></span>
                                <span class="text-danger">IP: {{ item.data.ip_address }}</span>
                            </p>
                            <p class="mb-0 text-muted" style="font-size: 0.7rem">{{ item.created_at }}</p>
                        </div>

                        <div class="flex-shrink-0 align-self-center">
                            <a :href="item.data.url" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.7rem"
                               @click.stop>
                                Review
                            </a>
                        </div>
                    </div>
                </template>

                <p v-else class="text-muted text-center py-4 mb-0 small">Tidak ada notifikasi</p>
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
const loading = ref(false);

let pollInterval = null;

async function fetchNotifications() {
    try {
        const res = await fetch('/api/internal/notifications', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();
        unreadCount.value = data.unread_count;
        items.value = data.items;
    } catch {
        // silent — network error during poll is non-critical
    }
}

function onOpen() {
    loading.value = true;
    fetchNotifications().finally(() => (loading.value = false));
}

async function markRead(item) {
    if (item.read) return;
    item.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    await fetch(`/api/internal/notifications/${item.id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

async function markAllRead() {
    items.value.forEach(i => (i.read = true));
    unreadCount.value = 0;
    await fetch('/api/internal/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

onMounted(() => {
    // Only poll when user is authenticated
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
    cursor: pointer;
    transition: background 0.15s;
}
.notification-item:hover {
    background: var(--bs-tertiary-bg) !important;
}
</style>
