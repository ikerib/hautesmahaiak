import { Controller } from '@hotwired/stimulus';
import { Datepicker } from 'flowbite-datepicker';
/* stimulusFetch: 'lazy' */
import DatepickerLocaleEs from "../lib/datepicker.locale.es.js";
import DatepickerLocaleEu from "../lib/datepicker.locale.eu.js";
export default class extends Controller {
    datepicker;
    connect() {
        this.element.type = 'text';
        Object.assign(Datepicker.locales, DatepickerLocaleEs, DatepickerLocaleEu);
        this.datepicker = new Datepicker(this.element, {
            format: 'yyyy-mm-dd',
            language:'eu',
            autohide: true,
            container: document.querySelector('dialog[open]') ? 'dialog[open]' : 'body'
        });

    }
    disconnect() {
        if (this.datepicker) {
            this.datepicker.destroy();
        }
        this.element.type = 'date';
    }
}
