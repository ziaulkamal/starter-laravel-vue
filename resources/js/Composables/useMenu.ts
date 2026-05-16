import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { NavItem } from '@/config/menu'

export function useMenu() {
    const page = usePage()

    // Strip query string so /users?page=2 still activates /users
    const currentPath = computed(() => page.url.split('?')[0])

    // Exact match — used for child items inside a dropdown
    function isActive(href: string): boolean {
        return currentPath.value === href
    }

    // Exact OR prefix match — used for flat (no-children) items
    function isPathActive(href: string): boolean {
        if (!href) return false
        return currentPath.value === href || currentPath.value.startsWith(href + '/')
    }

    // True when the group li should be highlighted + its collapse kept open
    function isGroupActive(item: NavItem): boolean {
        if (!item.children?.length) {
            return isPathActive(item.href ?? '')
        }
        return item.children.some(child => isPathActive(child.href))
    }

    function collapseId(href: string | null | undefined, label = ''): string {
        const key = href ?? label
        const slug = key.replace(/^\//, '').replace(/\//g, '-').replace(/\s+/g, '-').toLowerCase()
        return slug ? `menu-${slug}` : 'menu-root'
    }

    return { isActive, isPathActive, isGroupActive, collapseId }
}
