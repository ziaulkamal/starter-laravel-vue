/// <reference types="vite/client" />
/// <reference types="ziggy-js" />

import type { route as ziggyRoute } from 'ziggy-js'

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute
    }
}

declare module 'bootstrap' {
    export class Modal {
        constructor(element: Element | string, options?: object);
        static getInstance(element: Element | string): Modal | null;
        static getOrCreateInstance(element: Element | string, options?: object): Modal;
        show(): void;
        hide(): void;
        toggle(): void;
        dispose(): void;
    }
    export class Toast {
        constructor(element: Element | string, options?: object);
        static getInstance(element: Element | string): Toast | null;
        show(): void;
        hide(): void;
        dispose(): void;
    }
    export class Dropdown {
        constructor(element: Element | string, options?: object);
        static getInstance(element: Element | string): Dropdown | null;
        show(): void;
        hide(): void;
        toggle(): void;
        dispose(): void;
    }
}

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
        profile_menu: Array<{ label: string; icon: string | null; href: string }>
    }
}
