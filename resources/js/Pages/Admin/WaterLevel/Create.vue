<template>
    <AdminLayout>
        <Head title="Tambah Data Ketinggian Air" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="getCancelRoute()">
                                <ArrowLeft class="w-4 h-4" />
                            </Link>
                        </Button>
                        <div>
                            <CardTitle class="text-2xl">
                                Tambah Data Ketinggian Air
                                <span v-if="selectedPumpHouse" class="text-lg font-normal text-muted-foreground">
                                    - {{ selectedPumpHouse.name }}
                                </span>
                            </CardTitle>
                            <CardDescription>
                                <span v-if="selectedPumpHouse">
                                    Input data ketinggian air untuk {{ selectedPumpHouse.name }}
                                </span>
                                <span v-else>
                                    Input data ketinggian air rumah pompa
                                </span>
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Form -->
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Pump House Selection -->
                        <div class="space-y-2">
                            <Label for="pump_house_id">Rumah Pompa *</Label>
                            <!-- If coming from history page, show selected pump house info -->
                            <div v-if="selectedPumpHouse" class="p-3 bg-muted rounded-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">{{ selectedPumpHouse.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ selectedPumpHouse.address }}</div>
                                    </div>
                                    <Button 
                                        variant="outline" 
                                        size="sm"
                                        @click="clearSelectedPumpHouse"
                                    >
                                        Ganti
                                    </Button>
                                </div>
                            </div>
                            <!-- Normal dropdown when no pump house selected -->
                            <Select v-if="!selectedPumpHouse" v-model="form.pump_house_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Rumah Pompa" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="pumpHouse in pumpHouses" 
                                        :key="pumpHouse.id" 
                                        :value="pumpHouse.id.toString()"
                                    >
                                        {{ pumpHouse.name }} - {{ pumpHouse.address }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.pump_house_id" class="text-sm text-destructive">
                                {{ errors.pump_house_id }}
                            </p>
                        </div>

                        <!-- Water Level -->
                        <div class="space-y-2">
                            <Label for="water_level">Ketinggian Air (meter) *</Label>
                            <div class="relative">
                                <Input
                                    id="water_level"
                                    v-model="form.water_level"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="10"
                                    placeholder="Contoh: 1.25"
                                    class="pr-12"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-muted-foreground text-sm">meter</span>
                                </div>
                            </div>
                            <p v-if="errors.water_level" class="text-sm text-destructive">
                                {{ errors.water_level }}
                            </p>
                                
                                <!-- Water Level Status Indicator -->
                                <div v-if="form.water_level" class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div 
                                            :class="getStatusIndicatorClass(form.water_level)"
                                            class="w-3 h-3 rounded-full"
                                        ></div>
                                        <span 
                                            :class="getStatusTextClass(form.water_level)"
                                            class="text-sm font-medium"
                                        >
                                            {{ getStatusText(form.water_level) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ getStatusDescription(form.water_level) }}
                                    </p>
                                </div>
                            </div>

                        <!-- Recorded At -->
                        <div class="space-y-2">
                            <Label for="recorded_at">Waktu Pencatatan</Label>
                            <Input
                                id="recorded_at"
                                v-model="form.recorded_at"
                                type="datetime-local"
                            />
                            <p v-if="errors.recorded_at" class="text-sm text-destructive">
                                {{ errors.recorded_at }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Kosongkan untuk menggunakan waktu saat ini
                            </p>
                        </div>

                        <!-- Alert Warning -->
                        <Alert v-if="form.water_level >= 2.0" variant="destructive">
                            <AlertTriangle class="h-4 w-4" />
                            <AlertTitle>Peringatan</AlertTitle>
                            <AlertDescription>
                                Ketinggian air ini akan memicu alert otomatis ke sistem monitoring.
                            </AlertDescription>
                        </Alert>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-4 pt-6 border-t">
                            <Button variant="outline" as-child>
                                <Link :href="getCancelRoute()">
                                    Batal
                                </Link>
                            </Button>
                            <Button type="submit" :disabled="processing">
                                <Loader2 v-if="processing" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeft, AlertTriangle, Save, Loader2 } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'

const props = defineProps({
    pumpHouses: Array,
    selectedPumpHouse: Object,
    errors: Object,
})

// Reactive state for selected pump house
const selectedPumpHouse = ref(props.selectedPumpHouse)

const form = useForm({
    pump_house_id: selectedPumpHouse.value ? selectedPumpHouse.value.id.toString() : '',
    water_level: '',
    recorded_at: '',
    redirect_back: selectedPumpHouse.value ? 'history' : '',
})

const submit = () => {
    form.post(route('admin.water-level.store'))
}

const clearSelectedPumpHouse = () => {
    selectedPumpHouse.value = null
    form.pump_house_id = ''
    form.redirect_back = ''
}

const getCancelRoute = () => {
    if (selectedPumpHouse.value && selectedPumpHouse.value.id) {
        return route('admin.water-level.history', selectedPumpHouse.value.id)
    }
    return route('admin.water-level.index')
}

// Watch for changes in selectedPumpHouse to update form
watch(selectedPumpHouse, (newValue) => {
    if (newValue && newValue.id) {
        form.pump_house_id = newValue.id.toString()
        form.redirect_back = 'history'
    }
}, { immediate: true })

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

const { processing } = form
</script> 
 