import { onBeforeUnmount } from 'vue';
import { Modal } from 'bootstrap';

export function useBootstrapModal(modalIds: string[]) {
    function show(id: string): void {
        Modal.getOrCreateInstance(document.getElementById(id)!).show();
    }

    function hide(id: string): void {
        Modal.getOrCreateInstance(document.getElementById(id)!).hide();
    }

    onBeforeUnmount(() => {
        modalIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) Modal.getInstance(el)?.dispose();
        });
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });

    return { show, hide };
}
