import _ from 'lodash';
window._ = _;

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enhanced CSRF token management
const getCsrfToken = () => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
};

const updateCsrfToken = (newToken) => {
const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token && newToken) {
        token.content = newToken;
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        return true;
    }
    return false;
};

// Set initial CSRF token
const initialToken = getCsrfToken();
if (initialToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = initialToken;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Expose utility functions globally
window.getCsrfToken = getCsrfToken;
window.updateCsrfToken = updateCsrfToken;

// Tambahkan interceptor untuk menangani error
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Redirect ke halaman login jika tidak terautentikasi
            window.location.href = '/login';
        } else if (error.response && error.response.status === 419) {
            // CSRF token mismatch - try to refresh token first
            console.warn('CSRF token mismatch, attempting to refresh...');
            
            return fetch('/api/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token && window.updateCsrfToken(data.csrf_token)) {
                    console.log('CSRF token refreshed, retrying original request...');
                    // Retry the original request with new token
                    const originalConfig = error.config;
                    originalConfig.headers['X-CSRF-TOKEN'] = data.csrf_token;
                    return window.axios.request(originalConfig);
                } else {
                    throw new Error('Failed to update CSRF token');
                }
            })
            .catch(refreshError => {
                console.error('Failed to refresh CSRF token:', refreshError);
                window.location.reload();
            });
        }
        return Promise.reject(error);
    }
);