<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'frontend' => [
        'path' => './assets/frontend.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'flowbite' => [
        'version' => '2.2.1',
    ],
    'debounce' => [
        'version' => '2.0.0',
    ],
    'turbo-view-transitions' => [
        'version' => '0.3.0',
    ],
    'stimulus-use' => [
        'version' => '0.52.2',
    ],
    'flowbite-datepicker' => [
        'version' => '1.2.6',
    ],
    'flowbite/dist/flowbite.min.css' => [
        'version' => '2.2.1',
        'type' => 'css',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'trix' => [
        'version' => '2.0.10',
    ],
    'trix/dist/trix.min.css' => [
        'version' => '2.0.10',
        'type' => 'css',
    ],
    'flowbite/plugin' => [
        'version' => '2.3.0',
    ],
    'mini-svg-data-uri' => [
        'version' => '1.4.4',
    ],
    'tailwindcss/plugin' => [
        'version' => '3.4.1',
    ],
    'tailwindcss/defaultTheme' => [
        'version' => '3.4.1',
    ],
    'tailwindcss/colors' => [
        'version' => '3.4.1',
    ],
    'picocolors' => [
        'version' => '1.0.0',
    ],
];
