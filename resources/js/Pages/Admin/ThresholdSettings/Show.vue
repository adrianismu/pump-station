<template>
    <AdminLayout>
        <Head title="Detail Pengaturan Threshold" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="route('admin.threshold-settings.index')">
                                    <ArrowLeft class="w-4 h-4" />
                                </Link>
                            </Button>
                            <div>
                                <CardTitle class="text-2xl">Detail Pengaturan Threshold</CardTitle>
                                <CardDescription>
                                    Informasi lengkap threshold "{{ threshold.label }}"
                                </CardDescription>
                            </div>
                        </div>
                        <Button as-child>
                            <Link :href="route('admin.threshold-settings.edit', threshold.id)">
                                <Edit class="w-4 h-4 mr-2" />
                                Edit Threshold
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
            </Card>

            <!-- Threshold Preview -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Preview Threshold</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="p-4 border rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3">
                            <div 
                                :style="{ backgroundColor: threshold.color }"
                                class="w-5 h-5 rounded-full border"
                            ></div>
                            <div>
                                <div class="font-medium text-lg">{{ threshold.label }}</div>
                                <div class="text-sm text-muted-foreground">
                                    Ketinggian air ≥ {{ threshold.water_level }} meter
                                </div>
                                <div v-if="threshold.description" class="text-xs text-muted-foreground mt-1">
                                    {{ threshold.description }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Informasi Dasar</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Nama Internal</Label>
                            <div class="p-2 bg-muted rounded-md">
                                <code class="text-sm">{{ threshold.name }}</code>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Label Display</Label>
                            <p class="text-sm text-muted-foreground">{{ threshold.label }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Ketinggian Air</Label>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold">{{ threshold.water_level }}</span>
                                <span class="text-sm text-muted-foreground">meter</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Warna Indikator</Label>
                            <div class="flex items-center gap-3">
                                <div 
                                    class="w-8 h-8 rounded-md border border-border"
                                    :style="{ backgroundColor: threshold.color }"
                                ></div>
                                <div>
                                    <div class="text-sm font-medium">{{ threshold.color }}</div>
                                    <div class="text-xs text-muted-foreground">Hex Color Code</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status & Metadata -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Status & Metadata</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Tingkat Keparahan</Label>
                            <div class="flex items-center gap-2">
                                <div 
                                    class="w-3 h-3 rounded-full"
                                    :class="getSeverityColor(threshold.severity)"
                                ></div>
                                <Badge :variant="getSeverityVariant(threshold.severity)">
                                    {{ getSeverityLabel(threshold.severity) }}
                                </Badge>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Status Aktif</Label>
                            <div class="flex items-center gap-2">
                                <div 
                                    class="w-3 h-3 rounded-full"
                                    :class="threshold.is_active ? 'bg-green-500' : 'bg-red-500'"
                                ></div>
                                <Badge :variant="threshold.is_active ? 'default' : 'destructive'">
                                    {{ threshold.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </Badge>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Dibuat</Label>
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-muted-foreground" />
                                <p class="text-sm text-muted-foreground">{{ formatDate(threshold.created_at) }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium">Terakhir Diperbarui</Label>
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-muted-foreground" />
                                <p class="text-sm text-muted-foreground">{{ formatDate(threshold.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Description -->
            <Card v-if="threshold.description">
                <CardHeader>
                    <CardTitle class="text-lg">Deskripsi</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm leading-relaxed">{{ threshold.description }}</p>
                </CardContent>
            </Card>

            <!-- Usage Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Informasi Penggunaan</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <AlertTriangle class="w-5 h-5 text-amber-500 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium">Sistem Monitoring</p>
                                <p class="text-xs text-muted-foreground">
                                    Threshold ini digunakan untuk menentukan status ketinggian air dalam sistem monitoring otomatis.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Bell class="w-5 h-5 text-blue-500 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium">Notifikasi Alert</p>
                                <p class="text-xs text-muted-foreground">
                                    Ketika ketinggian air mencapai {{ threshold.water_level }}m atau lebih, sistem akan mengirim notifikasi dengan prioritas {{ getSeverityLabel(threshold.severity).toLowerCase() }}.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Palette class="w-5 h-5 text-purple-500 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium">Indikator Visual</p>
                                <p class="text-xs text-muted-foreground">
                                    Warna {{ threshold.color }} akan digunakan untuk menampilkan status ini di dashboard dan peta.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeft, Edit, Calendar, Clock, AlertTriangle, Bell, Palette } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import { Separator } from '@/Components/ui/separator'

defineProps({
    threshold: Object,
})

const getSeverityColor = (severity) => {
    const colors = {
        low: 'bg-green-500',
        medium: 'bg-yellow-500',
        high: 'bg-orange-500',
        critical: 'bg-red-500',
    }
    return colors[severity] || 'bg-gray-500'
}

const getSeverityVariant = (severity) => {
    const variants = {
        low: 'default',
        medium: 'secondary',
        high: 'destructive',
        critical: 'destructive',
    }
    return variants[severity] || 'secondary'
}

const getSeverityLabel = (severity) => {
    const labels = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        critical: 'Kritis',
    }
    return labels[severity] || severity
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script> 