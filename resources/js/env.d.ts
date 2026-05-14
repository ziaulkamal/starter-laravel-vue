/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent
    export default component
}

// Inertia shared props — tersedia via usePage().props di semua komponen
import type { NavEntry } from '@/config/menu'

declare module '@inertiajs/vue3' {
    interface PageProps {
        auth: {
            user: {
                id: number
                name: string
                email: string
            } | null
        }
        flash: {
            success: string | null
            error: string | null
        }
        menu: NavEntry[]
    }
}
