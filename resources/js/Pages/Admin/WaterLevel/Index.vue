<template>
    <AdminLayout>
        <Head title="Manajemen Ketinggian Air" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle class="text-2xl">Manajemen Ketinggian Air</CardTitle>
                            <CardDescription>Kelola data ketinggian air rumah pompa</CardDescription>
                        </div>
                        <Button as-child>
                            <Link :href="route('admin.water-level.create')">
                                <Plus class="w-4 h-4 mr-2" />
                                Tambah Data
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
            </Card>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <Droplets class="h-8 w-8 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">Total Rumah Pompa</p>
                                <p class="text-2xl font-semibold">{{ pumpHouses.length }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <AlertTriangle class="h-8 w-8 text-red-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">Level Kritis</p>
                                <p class="text-2xl font-semibold">{{ criticalCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <AlertCircle class="h-8 w-8 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">Level Peringatan</p>
                                <p class="text-2xl font-semibold">{{ warningCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CheckCircle class="h-8 w-8 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted-foreground">Level Normal</p>
                                <p class="text-2xl font-semibold">{{ normalCount }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pump Houses Status -->
            <Card>
                <CardHeader>
                    <CardTitle>Status Rumah Pompa</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card 
                            v-for="pumpHouse in pumpHouses" 
                            :key="pumpHouse.id"
                            class="hover:shadow-md transition-shadow"
                        >
                            <CardContent class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold">{{ pumpHouse.name }}</h4>
                                    <Badge 
                                        :variant="getStatusBadgeVariant(getCurrentWaterLevel(pumpHouse))"
                                    >
                                        {{ getStatusText(getCurrentWaterLevel(pumpHouse)) }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground mb-2">{{ pumpHouse.location }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-blue-600">
                                        {{ getCurrentWaterLevel(pumpHouse) }}m
                                    </span>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('admin.water-level.history', pumpHouse.id)">
                                            <BarChart3 class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent History -->
            <Card>
                <CardHeader>
                    <CardTitle>Data Terbaru</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <Button 
                                        variant="ghost" 
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                        @click="sort('pump_house_name')"
                                    >
                                        Rumah Pompa
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                        <ChevronUp v-if="filters.sort === 'pump_house_name' && filters.order === 'asc'" class="ml-1 h-3 w-3" />
                                        <ChevronDown v-if="filters.sort === 'pump_house_name' && filters.order === 'desc'" class="ml-1 h-3 w-3" />
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
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="history in recentHistory.data" :key="history.id">
                                <TableCell>
                                    <div class="font-medium">{{ history.pump_house.name }}</div>
                                    <div class="text-sm text-muted-foreground">{{ history.pump_house.location }}</div>
                                </TableCell>
                                <TableCell>
                                    <div class="font-bold text-blue-600">{{ history.water_level }}m</div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(history.water_level)">
                                        {{ getStatusText(history.water_level) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ formatDate(history.recorded_at) }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('admin.water-level.show', history.id)">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('admin.water-level.edit', history.id)">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm"
                                            @click="deleteRecord(history.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div v-if="recentHistory.links" class="flex justify-center mt-6">
                        <div class="flex gap-2">
                            <Button 
                                v-for="link in recentHistory.links" 
                                :key="link.label"
                                variant="outline"
                                size="sm"
                                :disabled="!link.url"
                                :class="{ 'bg-primary text-primary-foreground': link.active }"
                                @click="visitPage(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    Plus, 
    Droplets, 
    AlertTriangle, 
    AlertCircle, 
    CheckCircle, 
    BarChart3, 
    Eye, 
    Edit, 
    Trash2,
    ArrowUpDown,
    ChevronUp,
    ChevronDown
} from 'lucide-vue-next'
import { computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

const props = defineProps({
    pumpHouses: Array,
    recentHistory: Object,
    filters: Object,
})

const criticalCount = computed(() => {
    return props.pumpHouses.filter(house => getCurrentWaterLevel(house) >= 2.5).length
})

const warningCount = computed(() => {
    return props.pumpHouses.filter(house => {
        const level = getCurrentWaterLevel(house)
        return level >= 2.0 && level < 2.5
    }).length
})

const normalCount = computed(() => {
    return props.pumpHouses.filter(house => getCurrentWaterLevel(house) < 2.0).length
})

const getCurrentWaterLevel = (pumpHouse) => {
    if (pumpHouse.water_level_history && pumpHouse.water_level_history.length > 0) {
        return parseFloat(pumpHouse.water_level_history[0].water_level)
    }
    return 0
}

const getStatusText = (level) => {
    if (level >= 2.5) return 'Kritis'
    if (level >= 2.0) return 'Peringatan'
    return 'Normal'
}

const getStatusBadgeVariant = (level) => {
    if (level >= 2.5) return 'destructive'
    if (level >= 2.0) return 'secondary'
    return 'default'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const sort = (column) => {
    const currentSort = props.filters.sort
    const currentOrder = props.filters.order
    
    let newOrder = 'desc'
    if (currentSort === column && currentOrder === 'desc') {
        newOrder = 'asc'
    }
    
    router.get(route('admin.water-level.index'), {
        sort: column,
        order: newOrder
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const visitPage = (url) => {
    if (url) {
        router.visit(url, { preserveScroll: true })
    }
}

const deleteRecord = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('admin.water-level.destroy', id), {
            preserveScroll: true
        })
    }
}
</script> 
 