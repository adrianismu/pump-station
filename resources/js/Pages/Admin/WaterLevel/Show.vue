<template>
    <AdminLayout>
        <Head title="Detail Data Ketinggian Air" />
        
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
                            <CardTitle class="text-2xl">Detail Data Ketinggian Air</CardTitle>
                            <CardDescription>Informasi lengkap data ketinggian air</CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Water Level Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Main Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Droplets class="w-5 h-5 text-blue-600" />
                            Informasi Utama
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Rumah Pompa</Label>
                            <p class="text-lg font-semibold">{{ waterLevel.pump_house.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ waterLevel.pump_house.location }}</p>
                        </div>
                        
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Ketinggian Air</Label>
                            <div class="flex items-center gap-3">
                                <p class="text-3xl font-bold text-blue-600">{{ waterLevel.water_level }}m</p>
                                <Badge :variant="getStatusBadgeVariant(waterLevel.water_level)">
                                    {{ getStatusText(waterLevel.water_level) }}
                                </Badge>
                            </div>
                        </div>
                        
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Waktu Pencatatan</Label>
                            <p class="text-lg">{{ formatDate(waterLevel.recorded_at) }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status & Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Activity class="w-5 h-5 text-green-600" />
                            Status & Aksi
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Status Indicator -->
                        <div class="p-4 rounded-lg border" :class="getStatusCardClass(waterLevel.water_level)">
                            <div class="flex items-center gap-3">
                                <div :class="getStatusIndicatorClass(waterLevel.water_level)" class="w-4 h-4 rounded-full"></div>
                                <div>
                                    <p class="font-semibold" :class="getStatusTextClass(waterLevel.water_level)">
                                        {{ getStatusText(waterLevel.water_level) }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ getStatusDescription(waterLevel.water_level) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="space-y-3">
                            <Label class="text-sm font-medium text-muted-foreground">Aksi Cepat</Label>
                            <div class="flex flex-col gap-2">
                                <Button variant="outline" class="justify-start" as-child>
                                    <Link :href="route('admin.water-level.edit', waterLevel.id)">
                                        <Edit class="w-4 h-4 mr-2" />
                                        Edit Data
                                    </Link>
                                </Button>
                                <Button variant="outline" class="justify-start" as-child>
                                    <Link :href="route('admin.water-level.history', waterLevel.pump_house.id)">
                                        <BarChart3 class="w-4 h-4 mr-2" />
                                        Lihat Riwayat
                                    </Link>
                                </Button>
                                <Button 
                                    variant="outline" 
                                    class="justify-start text-red-600 hover:text-red-700"
                                    @click="deleteRecord"
                                >
                                    <Trash2 class="w-4 h-4 mr-2" />
                                    Hapus Data
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pump House Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MapPin class="w-5 h-5 text-purple-600" />
                        Informasi Rumah Pompa
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Nama</Label>
                            <p class="text-lg font-semibold">{{ waterLevel.pump_house.name }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Lokasi</Label>
                            <p class="text-lg">{{ waterLevel.pump_house.location }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Kapasitas</Label>
                            <p class="text-lg">{{ waterLevel.pump_house.capacity }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Jumlah Pompa</Label>
                            <p class="text-lg">{{ waterLevel.pump_house.pump_count }} unit</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Status Operasional</Label>
                            <Badge variant="default">{{ waterLevel.pump_house.operational_status }}</Badge>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Terakhir Diperbarui</Label>
                            <p class="text-lg">{{ formatDate(waterLevel.pump_house.last_updated) }}</p>
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
    ArrowLeft, 
    Droplets, 
    Activity, 
    MapPin, 
    Edit, 
    BarChart3, 
    Trash2 
} from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Label } from '@/Components/ui/label'

const props = defineProps({
    waterLevel: Object,
})

const getStatusText = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'Level Kritis'
    if (numLevel >= 2.0) return 'Level Peringatan'
    return 'Level Normal'
}

const getStatusDescription = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'Ketinggian air sangat tinggi, berpotensi banjir'
    if (numLevel >= 2.0) return 'Ketinggian air tinggi, perlu waspada'
    return 'Ketinggian air dalam batas normal'
}

const getStatusBadgeVariant = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'destructive'
    if (numLevel >= 2.0) return 'secondary'
    return 'default'
}

const getStatusIndicatorClass = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'bg-red-500'
    if (numLevel >= 2.0) return 'bg-yellow-500'
    return 'bg-green-500'
}

const getStatusTextClass = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'text-red-700'
    if (numLevel >= 2.0) return 'text-yellow-700'
    return 'text-green-700'
}

const getStatusCardClass = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'bg-red-50 border-red-200'
    if (numLevel >= 2.0) return 'bg-yellow-50 border-yellow-200'
    return 'bg-green-50 border-green-200'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const deleteRecord = () => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('admin.water-level.destroy', props.waterLevel.id), {
            onSuccess: () => {
                router.visit(route('admin.water-level.index'))
            }
        })
    }
}
</script> 
 