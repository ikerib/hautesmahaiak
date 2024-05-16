import { Controller } from '@hotwired/stimulus';
import { useTransition } from 'stimulus-use';

export default class extends Controller {
    static values = {
        auto: Number,
    };

    static targets = ['timerbar']

    connect() {
        useTransition(this, {
            leaveActive: 'transition ease-in duration-200',
            leaveFrom: 'opacity-100',
            leaveTo: 'opacity-0',
            transitioned: true,
        });

        if (this.autoValue) {
            setTimeout(() => {
                this.close();
            }, this.autoValue);

            if (this.hasTimerbarTarget) {
                setTimeout(() => {
                    this.timerbarTarget.style.width = 0;
                }, 10);
            }
        }
    }

    close() {
        if (this.hasTimerbarTarget) {
            this.timerbarTarget.remove();
        }

        this.leave();
    }
}
