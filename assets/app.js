import './bootstrap.js';

import { initFlowbite } from 'flowbite';
import './lib/darkmode.js'
// import './lib/datepicker.js'

import * as Turbo from '@hotwired/turbo';
import { shouldPerformTransition, performTransition } from 'turbo-view-transitions';
import Trix from "trix"

//Turbo.session.drive = false;

let skipNextRenderTransition = false;
document.addEventListener('turbo:before-render', (event) => {
    if (shouldPerformTransition() && !skipNextRenderTransition) {
        event.preventDefault();

        performTransition(document.body, event.detail.newBody, async () => {
            await event.detail.resume();
        });
    }
});

document.addEventListener('turbo:load', () => {
    // View Transitions don't play nicely with Turbo cache
    if (shouldPerformTransition()) Turbo.cache.exemptPageFromCache();
});

document.addEventListener('turbo:before-frame-render', (event) => {
    if (shouldPerformTransition() && !event.target.hasAttribute('data-skip-transition')) {
        event.preventDefault();

        // workaround for data-turbo-action="advance", which triggers
        // turbo:before-render (and we want THAT to not try to transition)
        skipNextRenderTransition = true;
        setTimeout(() => {
            skipNextRenderTransition = false;
        }, 100);

        performTransition(event.target, event.detail.newFrame, async () => {
            await event.detail.resume();
        });
    }
});

document.addEventListener('turbo:render', () => {
    initFlowbite();
});
document.addEventListener('turbo:frame-render', () => {
    initFlowbite();
});
