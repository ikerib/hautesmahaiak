import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog', 'modalEdukia', 'loadingTemplate'];
    observer = null;

    connect() {
        this.observer = new MutationObserver(() => {
            const shouldOpen = this.modalEdukiaTarget.innerHTML.trim().length > 0;

            if (shouldOpen && !this.dialogTarget.open) {
                this.open();
            } else if (!shouldOpen && this.dialogTarget.open) {
                this.close();
            }
        });
        this.observer.observe(this.modalEdukiaTarget, { childList: true, characterData: true, subtree: true });
    }

    disconnect() {
        this.observer.disconnect();
        if (this.dialogTarget.open) {
            this.close();
        }
    }

    open() {
        this.dialogTarget.showModal();
        document.body.classList.add('overflow-hidden', 'blur-sm');
    }

    close() {
        this.dialogTarget.close();
        document.body.classList.remove('overflow-hidden', 'blur-sm');
        // location.reload();
    }

    clickOutside(event) {
        if ( event.target !== this.dialogTarget ) {
            return;
        }

        if (!this.#isClickInElement(event, this.dialogTarget)) {
            this.dialogTarget.close();
        }
    }

    showLoading(event) {
        // do nothing if the dialog is already open
        if (this.dialogTarget.open) {
            return;
        }

        this.modalEdukiaTarget.innerHTML = this.loadingTemplateTarget.innerHTML;
    }

    #isClickInElement(event, element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top <= event.clientY &&
            event.clientY <= rect.top + rect.height &&
            rect.left <= event.clientX &&
            event.clientX <= rect.left + rect.width
        );
    }
}
