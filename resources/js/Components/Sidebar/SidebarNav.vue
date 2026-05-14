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
                       data-bs-toggle="collapse"
                       :data-bs-target="'#' + collapseId(entry.href)"
                       :aria-expanded="isGroupActive(entry)">
                        <span class="d-flex"><i :class="entry.icon"></i></span>
                        <span class="hide-menu">{{ entry.label }}</span>
                    </a>
                    <ul :id="collapseId(entry.href)"
                        class="collapse first-level"
                        data-bs-parent="#sidebarnav"
                        :class="{ show: isGroupActive(entry) }">
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
                <li v-else
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
import { computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SimpleBar from 'simplebar';
import { useMenu } from '@/Composables/useMenu';

const menu = computed(() => usePage().props.menu ?? []);
const { isActive, isPathActive, isGroupActive, collapseId } = useMenu();

onMounted(() => {
    const scrollEl = document.querySelector('.scroll-sidebar');
    if (scrollEl) new SimpleBar(scrollEl);
});
</script>
