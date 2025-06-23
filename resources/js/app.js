import './bootstrap';
import '../css/app.css';
import './lib/csrfManager';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Sistem Monitoring Rumah Pompa';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, window.Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Handle Inertia CSRF errors globally
import { router } from '@inertiajs/vue3'

// Enhanced CSRF error handling with retry mechanism
router.on('error', (event) => {
    const response = event.detail.response;
    
    if (response.status === 419) {
        console.warn('CSRF token mismatch detected, attempting to refresh...');
        
        // Try to refresh CSRF token first
        fetch('/api/csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                // Update meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }
                
                // Update axios default header
                if (window.axios) {
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token;
                }
                
                console.log('CSRF token refreshed successfully');
                
                // Show user-friendly message instead of reloading
                if (window.showToast) {
                    window.showToast('Session telah diperbarui, silakan coba lagi', 'warning');
                } else {
                    alert('Session telah diperbarui, silakan coba lagi');
                }
            } else {
                // Fallback to reload if token refresh fails
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Failed to refresh CSRF token:', error);
            window.location.reload();
        });
    }
});

// Auto-update CSRF token from response headers
router.on('success', (event) => {
    const response = event.detail.response;
    if (response && response.headers) {
        const newToken = response.headers['x-csrf-token'] || response.headers['X-CSRF-TOKEN'];
        if (newToken && window.updateCsrfToken) {
            window.updateCsrfToken(newToken);
        }
    }
});