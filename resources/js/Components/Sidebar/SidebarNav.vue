<template>
    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
        <ul id="sidebarnav">

            <template v-for="entry in menu" :key="entry.label">

                <!-- Section header -->
                <li v-if="entry.type === 'section'" class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">{{ entry.label }}</span>
                </li>

                <!-- Item dengan submenu (collapsible) -->
                <li v-else-if="entry.children?.length"
                    class="sidebar-item"
                    :class="{ active: isGroupActive(entry) }">
                    <a class="sidebar-link has-arrow"
                       href="javascript:void(0)"
                       :aria-expanded="openId === collapseId(entry.href, entry.label)"
                       @click.prevent="toggle(entry)">
                        <span class="d-flex"><i :class="entry.icon"></i></span>
                        <span class="hide-menu">{{ entry.label }}</span>
                    </a>
                    <ul :id="collapseId(entry.href, entry.label)"
                        class="collapse first-level">
                        <li v-for="child in entry.children"
                            :key="child.href"
                            class="sidebar-item"
                            :class="{ active: isActive(child.href) }">
                            <Link :href="child.href" class="sidebar-link">
                                <div class="round-16 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-circle"></i>
                                </div>
                                <span class="hide-menu">{{ child.label }}</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Item flat (tanpa submenu) -->
                <li v-else-if="entry.href"
                    class="sidebar-item"
                    :class="{ active: isPathActive(entry.href) }">
                    <Link class="sidebar-link" :href="entry.href">
                        <span><i :class="entry.icon"></i></span>
                        <span class="hide-menu">{{ entry.label }}</span>
                    </Link>
                </li>

            </template>

            <!-- slot untuk inject menu context-specific dari luar -->
            <slot />

        </ul>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Collapse } from 'bootstrap';
import SimpleBar from 'simplebar';
import { useMenu } from '@/Composables/useMenu';

const menu    = computed(() => usePage().props.menu ?? []);
const page    = usePage();
const { isActive, isPathActive, isGroupActive, collapseId } = useMenu();

// ID of the currently open collapse group
const openId = ref(null);

// Find the group that matches the current route
function resolveActiveId() {
    for (const entry of menu.value) {
        if (entry.children?.length && isGroupActive(entry)) {
            return collapseId(entry.href, entry.label);
        }
    }
    return null;
}

// Open a collapse by id without animation (for initial load)
function openInitial(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('show');
}

// Bootstrap-animated toggle: close current, open target
function toggle(entry) {
    const targetId = collapseId(entry.href, entry.label);

    if (openId.value && openId.value !== targetId) {
        // Close currently open group
        const prevEl = document.getElementById(openId.value);
        if (prevEl) Collapse.getOrCreateInstance(prevEl, { toggle: false }).hide();
    }

    const targetEl = document.getElementById(targetId);
    if (!targetEl) return;

    if (openId.value === targetId) {
        // Close self
        Collapse.getOrCreateInstance(targetEl, { toggle: false }).hide();
        openId.value = null;
    } else {
        // Open target
        Collapse.getOrCreateInstance(targetEl, { toggle: false }).show();
        openId.value = targetId;
    }
}

onMounted(async () => {
    // Init SimpleBar
    const scrollEl = document.querySelector('.scroll-sidebar');
    if (scrollEl) new SimpleBar(scrollEl);

    // Open active group immediately (no animation on first load)
    const activeId = resolveActiveId();
    openId.value = activeId;
    if (activeId) openInitial(activeId);
});

// Keep in sync on Inertia navigation (SPA)
watch(() => page.url, async () => {
    await nextTick();
    const activeId = resolveActiveId();
    if (activeId && activeId !== openId.value) {
        // Close previous
        if (openId.value) {
            const prevEl = document.getElementById(openId.value);
            if (prevEl) prevEl.classList.remove('show');
        }
        openId.value = activeId;
        openInitial(activeId);
    }
});
</script>
