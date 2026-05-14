export interface NavSection {
    type: 'section'
    label: string
}

export interface NavChild {
    label: string
    href: string
}

export interface NavItem {
    type: 'item'
    label: string
    icon: string
    href: string
    children?: NavChild[]
}

export type NavEntry = NavSection | NavItem

export const menu: NavEntry[] = [

    // ── Home ──────────────────────────────────────────────
    { type: 'section', label: 'Home' },
    {
        type: 'item',
        label: 'Dashboard',
        icon: 'ti ti-layout-dashboard',
        href: '/',
    },

    // ── Management ────────────────────────────────────────
    { type: 'section', label: 'Management' },
    {
        type: 'item',
        label: 'Users',
        icon: 'ti ti-users',
        href: '/users',
        children: [
            { label: 'User List',    href: '/users' },
            { label: 'Create User',  href: '/users/create' },
        ],
    },
    {
        type: 'item',
        label: 'Roles & Permissions',
        icon: 'ti ti-shield-lock',
        href: '/roles',
        children: [
            { label: 'Role List',    href: '/roles' },
            { label: 'Permissions',  href: '/permissions' },
        ],
    },

    // ── Pages ─────────────────────────────────────────────
    { type: 'section', label: 'Pages' },
    {
        type: 'item',
        label: 'Profile',
        icon: 'ti ti-user-circle',
        href: '/profile',
    },
    {
        type: 'item',
        label: 'Settings',
        icon: 'ti ti-settings',
        href: '/settings',
    },

]
