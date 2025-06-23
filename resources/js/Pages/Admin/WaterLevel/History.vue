<template>
    <AdminLayout>
        <Head :title="`Riwayat Ketinggian Air - ${pumpHouse.name}`" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="route('admin.water-level.index')">
                                <ArrowLeft class="w-4 h-4" />
                            </Link>
                        </Button>
                        <div>
                            <CardTitle class="text-2xl">Riwayat Ketinggian Air</CardTitle>
                            <CardDescription>{{ pumpHouse.name }} - {{ pumpHouse.location }}</CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

 

            <!-- Chart Section -->
            <ChartWithTimeFilter
                :data="history.data || []"
                :thresholds="activeThresholds"
                :title="`Grafik Ketinggian Air - ${pumpHouse.name}`"
                :description="`Visualisasi data ketinggian air untuk ${pumpHouse.name}`"
                :height="320"
            />

            <!-- History Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="w-5 h-5 text-purple-600" />
                            Riwayat Data
                        </CardTitle>
                        <Button as-child>
                            <Link :href="route('admin.water-level.create', { pump_house_id: pumpHouse.id })">
                                <Plus class="w-4 h-4 mr-2" />
                                Tambah Data
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <Button 
                                        variant="ghost" 
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                        @click="sort('recorded_at')"
                                    >
                                        Waktu Pencatatan
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                        <ChevronUp v-if="filters.sort === 'recorded_at' && filters.order === 'asc'" class="ml-1 h-3 w-3" />
                                        <ChevronDown v-if="filters.sort === 'recorded_at' && filters.order === 'desc'" class="ml-1 h-3 w-3" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button 
                                        variant="ghost" 
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                        @click="sort('water_level')"
                                    >
                                        Ketinggian Air
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                        <ChevronUp v-if="filters.sort === 'water_level' && filters.order === 'asc'" class="ml-1 h-3 w-3" />
                                        <ChevronDown v-if="filters.sort === 'water_level' && filters.order === 'desc'" class="ml-1 h-3 w-3" />
                                    </Button>
                                </TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="record in history.data" :key="record.id">
                                <TableCell>
                                    <div class="font-medium">{{ formatDate(record.recorded_at) }}</div>
                                    <div class="text-sm text-muted-foreground">{{ formatTime(record.recorded_at) }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-bold text-blue-600 text-lg">{{ record.water_level }}m</div>
                                </TableCell>
                                <TableCell>
                                    <StatusBadge 
                                        :level="record.water_level"
                                        :thresholds="activeThresholds"
                                        type="water-level"
                                    />
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('admin.water-level.show', record.id)">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('admin.water-level.edit', record.id)">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm"
                                            @click="deleteRecord(record.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <DataPagination :links="history.links" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    ArrowLeft, 
    MapPin, 
    BarChart3, 
    Clock, 
    Plus, 
    Eye, 
    Edit, 
    Trash2,
    ArrowUpDown,
    ChevronUp,
    ChevronDown
} from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

import ChartWithTimeFilter from '@/Components/Charts/ChartWithTimeFilter.vue'
import StatusBadge from '@/Components/ui/StatusBadge.vue'
import DataPagination from '@/Components/ui/DataPagination.vue'
import { useDateUtils } from '@/composables/useDateUtils'
import { useTableActions } from '@/composables/useTableActions'
import { computed } from 'vue'

const props = defineProps({
    pumpHouse: Object,
    history: Object,
    filters: Object,
    thresholds: {
        type: Array,
        default: () => []
    },
    globalThresholds: {
        type: Array,
        default: () => []
    }
})

// Use composables
const { formatDate, formatTime } = useDateUtils()
const { sort: sortTable, deleteRecord } = useTableActions('admin.water-level.history')

// Computed properties
const activeThresholds = computed(() => {
    return props.thresholds.length > 0 ? props.thresholds : props.globalThresholds
})

// Custom sort function for this page
const sort = (column) => {
    sortTable(column, props.filters)
}

// onMounted is no longer needed for chart loading
</script> 
 