import { usePage } from '@inertiajs/vue3'
import type { NavItem } from '@/config/menu'

export function useMenu() {
    const page = usePage()

    function isActive(href: string): boolean {
        return page.url === href
    }

    function isGroupActive(item: NavItem): boolean {
        if (!item.children) return isActive(item.href)
        return item.children.some(
            (child) => page.url === child.href || page.url.startsWith(child.href + '/'),
        )
    }

    // '/users' → 'menu-users' | '/roles' → 'menu-roles'
    function collapseId(href: string): string {
        const slug = href.replace(/^\//, '').replace(/\//g, '-')
        return slug ? `menu-${slug}` : 'menu-root'
    }

    return { isActive, isGroupActive, collapseId }
}
