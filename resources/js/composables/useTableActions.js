import { router } from '@inertiajs/vue3'

// Composable untuk table actions
export function useTableActions(routeName, options = {}) {
  const {
    confirmDeleteMessage = 'Apakah Anda yakin ingin menghapus data ini?',
    preserveScroll = true,
    preserveState = true
  } = options

  // Sort function
  const sort = (column, currentFilters = {}) => {
    const currentSort = currentFilters.sort
    const currentOrder = currentFilters.order
    
    let newOrder = 'desc'
    if (currentSort === column && currentOrder === 'desc') {
      newOrder = 'asc'
    }
    
    router.get(route(routeName), {
      ...currentFilters,
      sort: column,
      order: newOrder
    }, {
      preserveState,
      preserveScroll
    })
  }

  // Delete function
  const deleteRecord = (id, deleteRouteName = null) => {
    if (confirm(confirmDeleteMessage)) {
      const routeToUse = deleteRouteName || `${routeName.replace('.index', '')}.destroy`
      
      router.delete(route(routeToUse, id), {
        preserveScroll
      })
    }
  }

  // Visit page function for pagination
  const visitPage = (url) => {
    if (url) {
      router.visit(url, { preserveScroll })
    }
  }

  // Filter function
  const applyFilters = (filters, currentFilters = {}) => {
    router.get(route(routeName), {
      ...currentFilters,
      ...filters
    }, {
      preserveState,
      preserveScroll
    })
  }

  return {
    sort,
    deleteRecord,
    visitPage,
    applyFilters
  }
} 