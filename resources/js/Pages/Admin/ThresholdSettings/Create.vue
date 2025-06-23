<template>
    <AdminLayout>
        <Head title="Tambah Pengaturan Threshold" />
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">Tambah Pengaturan Threshold</h2>
                            <Link :href="route('admin.threshold-settings.index')" 
                                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Kembali
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
                                <input
                                    id="label"
                                    v-model="form.label"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <div v-if="form.errors.label" class="text-red-600 text-sm mt-1">{{ form.errors.label }}</div>
                            </div>

                            <div>
                                <label for="water_level" class="block text-sm font-medium text-gray-700">Ketinggian Air (m)</label>
                                <input
                                    id="water_level"
                                    v-model="form.water_level"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <div v-if="form.errors.water_level" class="text-red-600 text-sm mt-1">{{ form.errors.water_level }}</div>
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700">Warna</label>
                                <input
                                    id="color"
                                    v-model="form.color"
                                    type="color"
                                    class="mt-1 block w-20 h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <div v-if="form.errors.color" class="text-red-600 text-sm mt-1">{{ form.errors.color }}</div>
                            </div>

                            <div>
                                <label for="severity" class="block text-sm font-medium text-gray-700">Tingkat Keparahan</label>
                                <select
                                    id="severity"
                                    v-model="form.severity"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Pilih Tingkat Keparahan</option>
                                    <option value="low">Rendah</option>
                                    <option value="medium">Sedang</option>
                                    <option value="high">Tinggi</option>
                                    <option value="critical">Kritis</option>
                                </select>
                                <div v-if="form.errors.severity" class="text-red-600 text-sm mt-1">{{ form.errors.severity }}</div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                            </div>

                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif</label>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const form = useForm({
    name: '',
    label: '',
    water_level: '',
    color: '#10b981',
    severity: '',
    description: '',
    is_active: true,
})

const submit = () => {
    form.post(route('admin.threshold-settings.store'))
}
</script> 