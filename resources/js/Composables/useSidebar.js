import { onMounted, onBeforeUnmount } from 'vue';

// [CHANGED] 1300 → 992: sinkron dengan media-breakpoint-up(lg) di CSS
const XL_BREAKPOINT = 992;

export function useSidebar() {
    function isMobile() {
        return window.innerWidth < XL_BREAKPOINT;
    }

    function toggleSidebar() {
        if (isMobile()) {
            document.getElementById('main-wrapper')?.classList.toggle('show-sidebar');
        } else {
            const current = document.body.getAttribute('data-sidebartype');
            document.body.setAttribute('data-sidebartype', current === 'full' ? 'mini-sidebar' : 'full');
        }
    }

    function closeMobileSidebar() {
        document.getElementById('main-wrapper')?.classList.remove('show-sidebar');
    }

    function onResize() {
        if (!isMobile()) closeMobileSidebar();
    }

    onMounted(() => window.addEventListener('resize', onResize));
    onBeforeUnmount(() => window.removeEventListener('resize', onResize));

    return { toggleSidebar, closeMobileSidebar };
}
