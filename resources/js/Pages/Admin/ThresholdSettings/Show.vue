<template>
    <AdminLayout>
        <Head title="Detail Pengaturan Threshold" />
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">Detail Pengaturan Threshold</h2>
                            <div class="space-x-2">
                                <Link :href="route('admin.threshold-settings.edit', threshold.id)" 
                                      class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Edit
                                </Link>
                                <Link :href="route('admin.threshold-settings.index')" 
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Kembali
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ threshold.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Label</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ threshold.label }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ketinggian Air</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ threshold.water_level }} meter</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Warna</label>
                                    <div class="mt-1 flex items-center space-x-2">
                                        <div 
                                            class="w-6 h-6 rounded border border-gray-300"
                                            :style="{ backgroundColor: threshold.color }"
                                        ></div>
                                        <span class="text-sm text-gray-900">{{ threshold.color }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tingkat Keparahan</label>
                                    <span 
                                        class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getSeverityClass(threshold.severity)"
                                    >
                                        {{ getSeverityLabel(threshold.severity) }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <span 
                                        class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="threshold.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ threshold.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(threshold.created_at) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Diperbarui</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(threshold.updated_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="threshold.description" class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ threshold.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    threshold: Object,
})

const getSeverityClass = (severity) => {
    const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800',
    }
    return classes[severity] || 'bg-gray-100 text-gray-800'
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