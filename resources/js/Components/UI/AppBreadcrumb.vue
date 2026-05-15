<template>
    <div class="app-page-header d-flex align-items-start justify-content-between mb-4">
        <!-- Left: title + nav -->
        <div>
            <h4 v-if="resolvedTitle" class="fw-semibold mb-8">{{ resolvedTitle }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li
                        v-for="(crumb, i) in resolvedCrumbs"
                        :key="i"
                        class="breadcrumb-item"
                        :aria-current="i === resolvedCrumbs.length - 1 ? 'page' : undefined"
                    >
                        <Link
                            v-if="crumb.href"
                            :href="crumb.href"
                            class="text-muted text-decoration-none"
                        >
                            {{ crumb.label }}
                        </Link>
                        <span v-else>{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Right: action buttons slot -->
        <div v-if="$slots.actions" class="flex-shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

interface Props {
    /** Judul halaman (h4). Auto-generate dari URL jika tidak diisi. */
    title?: string;
    /** Override auto-generate crumbs. Home selalu ditambah di awal. */
    items?: BreadcrumbItem[];
}

const props = defineProps<Props>();

const page = usePage();

// ─── Helpers ─────────────────────────────────────────────────────────────────

function formatSegment(segment: string): string {
    return segment
        .replace(/[-_]/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/\b\w/g, c => c.toUpperCase());
}

function currentPathname(): string {
    return page.url.split('?')[0];
}

// ─── Resolved values ──────────────────────────────────────────────────────────

const resolvedTitle = computed<string>(() => {
    if (props.title) return props.title;
    const segments = currentPathname().split('/').filter(Boolean);
    return segments.length > 0 ? formatSegment(segments[segments.length - 1]) : '';
});

const resolvedCrumbs = computed<BreadcrumbItem[]>(() => {
    const home: BreadcrumbItem = { label: 'Home', href: '/' };

    // Custom items override
    if (props.items && props.items.length > 0) {
        return [home, ...props.items];
    }

    // Auto-generate from URL
    const segments = currentPathname().split('/').filter(Boolean);
    if (segments.length === 0) return [home];

    const crumbs: BreadcrumbItem[] = [home];
    let buildPath = '';

    segments.forEach((segment, idx) => {
        buildPath += `/${segment}`;
        const isLast = idx === segments.length - 1;
        crumbs.push({
            label: isLast && props.title ? props.title : formatSegment(segment),
            href: isLast ? undefined : buildPath,
        });
    });

    return crumbs;
});
</script>

<style lang="scss" scoped>
.app-page-header {
    .breadcrumb-item {
        font-size: 0.8125rem;

        + .breadcrumb-item::before {
            color: var(--bs-secondary-color);
        }

        a {
            transition: color 0.15s ease;

            &:hover {
                color: var(--bs-primary) !important;
            }
        }

        &.active {
            color: var(--bs-body-color);
            font-weight: 500;
        }
    }
}
</style>
