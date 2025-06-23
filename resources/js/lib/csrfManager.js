class CsrfManager {
    constructor() {
        this.refreshInterval = null
        this.refreshIntervalTime = 30 * 60 * 1000 // 30 minutes
        this.isRefreshing = false
        this.init()
    }

    init() {
        this.startPeriodicRefresh()
        
        // Refresh on page focus (when user comes back to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && !this.isRefreshing) {
                this.refreshToken()
            }
        })

        // Refresh before page unload for the next visit
        window.addEventListener('beforeunload', () => {
            this.refreshToken()
        })
    }

    async refreshToken() {
        if (this.isRefreshing) return

        this.isRefreshing = true
        
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })

            if (response.ok) {
                const data = await response.json()
                if (data.csrf_token && window.updateCsrfToken) {
                    window.updateCsrfToken(data.csrf_token)
                    console.log('CSRF token refreshed automatically')
                    return true
                }
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error)
        } finally {
            this.isRefreshing = false
        }

        return false
    }

    startPeriodicRefresh() {
        // Clear existing interval
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval)
        }

        // Set new interval
        this.refreshInterval = setInterval(() => {
            this.refreshToken()
        }, this.refreshIntervalTime)
    }

    stopPeriodicRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval)
            this.refreshInterval = null
        }
    }

    destroy() {
        this.stopPeriodicRefresh()
        document.removeEventListener('visibilitychange', this.handleVisibilityChange)
        window.removeEventListener('beforeunload', this.handleBeforeUnload)
    }
}

// Create global instance
let csrfManager = null

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        csrfManager = new CsrfManager()
    })
} else {
    csrfManager = new CsrfManager()
}

export default CsrfManager 