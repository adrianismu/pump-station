import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export function useRetryableRequest() {
    const isRetrying = ref(false)
    const maxRetries = 3
    let retryCount = 0

    const makeRequest = async (method, url, data = {}, options = {}) => {
        const performRequest = () => {
            return new Promise((resolve, reject) => {
                const requestOptions = {
                    ...options,
                    onSuccess: (response) => {
                        retryCount = 0 // Reset retry count on success
                        if (options.onSuccess) options.onSuccess(response)
                        resolve(response)
                    },
                    onError: async (errors) => {
                        // Check if it's a CSRF error (419) and we haven't exceeded max retries
                        if (errors.response?.status === 419 && retryCount < maxRetries) {
                            retryCount++
                            isRetrying.value = true
                            
                            console.warn(`CSRF error detected, retry attempt ${retryCount}/${maxRetries}`)
                            
                            try {
                                // Wait a bit before retrying
                                await new Promise(resolve => setTimeout(resolve, 1000))
                                
                                // Get fresh CSRF token
                                const response = await fetch('/api/csrf-token', {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                })
                                
                                if (response.ok) {
                                    const tokenData = await response.json()
                                    
                                    if (tokenData.csrf_token && window.updateCsrfToken) {
                                        window.updateCsrfToken(tokenData.csrf_token)
                                        
                                        // Retry the request
                                        setTimeout(() => {
                                            isRetrying.value = false
                                            performRequest()
                                        }, 500)
                                        return
                                    }
                                }
                            } catch (refreshError) {
                                console.error('Failed to refresh CSRF token:', refreshError)
                            }
                            
                            isRetrying.value = false
                        }
                        
                        // If not a CSRF error or max retries exceeded, pass through the error
                        if (options.onError) options.onError(errors)
                        reject(errors)
                    },
                    onFinish: () => {
                        isRetrying.value = false
                        if (options.onFinish) options.onFinish()
                    }
                }

                // Make the request using Inertia router
                if (method === 'get') {
                    router.get(url, requestOptions)
                } else if (method === 'post') {
                    router.post(url, data, requestOptions)
                } else if (method === 'put') {
                    router.put(url, data, requestOptions)
                } else if (method === 'patch') {
                    router.patch(url, data, requestOptions)
                } else if (method === 'delete') {
                    router.delete(url, requestOptions)
                }
            })
        }

        return performRequest()
    }

    return {
        isRetrying,
        get: (url, options) => makeRequest('get', url, {}, options),
        post: (url, data, options) => makeRequest('post', url, data, options),
        put: (url, data, options) => makeRequest('put', url, data, options),
        patch: (url, data, options) => makeRequest('patch', url, data, options),
        delete: (url, options) => makeRequest('delete', url, {}, options),
    }
} 