import { usePage } from '@inertiajs/vue3'

export function useCsrf() {
    const page = usePage()
    
    const getCsrfToken = () => {
        return page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
    
    const refreshCsrfToken = async () => {
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            
            if (response.ok) {
                const data = await response.json()
                const metaTag = document.querySelector('meta[name="csrf-token"]')
                if (metaTag && data.csrf_token) {
                    metaTag.setAttribute('content', data.csrf_token)
                    if (window.axios) {
                        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token
                    }
                }
                return data.csrf_token
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error)
        }
        return null
    }
    
    return {
        getCsrfToken,
        refreshCsrfToken
    }
} 