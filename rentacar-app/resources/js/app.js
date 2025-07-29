import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { router } from '@inertiajs/vue3';
import '../css/utilidades.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Configurar el prefijo base para Inertia
router.on('before', (event) => {
    const basePath = '/inf513/grupo20sa/proyecto2/proyecto2-tecno/rentacar-app/public';
    if (!event.detail.visit.url.startsWith(basePath)) {
        event.detail.visit.url = basePath + event.detail.visit.url;
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});