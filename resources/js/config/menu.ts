// Type definitions untuk struktur menu.
// Data menu dikelola di database — lihat App\Models\Menu dan MenuSeeder.

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
