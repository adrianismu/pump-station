<template>
    <AdminLayout>
        <Head title="Tambah Pengaturan Threshold" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="route('admin.threshold-settings.index')">
                                <ArrowLeft class="w-4 h-4" />
                            </Link>
                        </Button>
                        <div>
                            <CardTitle class="text-2xl">Tambah Pengaturan Threshold</CardTitle>
                            <CardDescription>
                                Buat pengaturan threshold baru untuk monitoring ketinggian air
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Form -->
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Nama *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Peringatan, Kritis, dll"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Nama internal untuk threshold (tanpa spasi, huruf kecil)
                            </p>
                        </div>

                        <!-- Label -->
                        <div class="space-y-2">
                            <Label for="label">Label *</Label>
                            <Input
                                id="label"
                                v-model="form.label"
                                type="text"
                                placeholder="Contoh: Peringatan, Kritis, Normal"
                                required
                            />
                            <p v-if="form.errors.label" class="text-sm text-destructive">
                                {{ form.errors.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Label yang akan ditampilkan kepada user
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
                                    placeholder="Contoh: 2.50"
                                    class="pr-12"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-muted-foreground text-sm">meter</span>
                                </div>
                            </div>
                            <p v-if="form.errors.water_level" class="text-sm text-destructive">
                                {{ form.errors.water_level }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Ketinggian air minimum untuk memicu threshold ini
                            </p>
                        </div>

                        <!-- Color -->
                        <div class="space-y-2">
                            <Label for="color">Warna *</Label>
                            <div class="flex items-center gap-4">
                                <Input
                                    id="color"
                                    v-model="form.color"
                                    type="color"
                                    class="w-20 h-10"
                                    required
                                />
                                <div class="flex items-center gap-2">
                                    <div 
                                        :style="{ backgroundColor: form.color }"
                                        class="w-4 h-4 rounded-full border border-border"
                                    ></div>
                                    <span class="text-sm text-muted-foreground">{{ form.color }}</span>
                                </div>
                            </div>
                            <p v-if="form.errors.color" class="text-sm text-destructive">
                                {{ form.errors.color }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Warna yang akan digunakan untuk status dan indikator
                            </p>
                        </div>

                        <!-- Severity -->
                        <div class="space-y-2">
                            <Label for="severity">Tingkat Keparahan *</Label>
                            <Select v-model="form.severity" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih tingkat keparahan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="low">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                            <span>Rendah</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="medium">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                            <span>Sedang</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="high">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                                            <span>Tinggi</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="critical">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            <span>Kritis</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.severity" class="text-sm text-destructive">
                                {{ form.errors.severity }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Menentukan prioritas notifikasi dan alert
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Deskripsi</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Deskripsi detail tentang threshold ini..."
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Opsional - Penjelasan detail tentang kondisi threshold
                            </p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                id="is_active" 
                                v-model:checked="form.is_active"
                            />
                            <Label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Aktifkan threshold ini
                            </Label>
                        </div>

                        <!-- Preview Card -->
                        <div v-if="form.name || form.label" class="space-y-2">
                            <Label>Preview</Label>
                            <div class="p-4 border rounded-lg bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <div 
                                        :style="{ backgroundColor: form.color }"
                                        class="w-4 h-4 rounded-full"
                                    ></div>
                                    <div>
                                        <div class="font-medium">{{ form.label || 'Label threshold' }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            Ketinggian air ≥ {{ form.water_level || '0' }} meter
                                        </div>
                                        <div v-if="form.description" class="text-xs text-muted-foreground mt-1">
                                            {{ form.description }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-4 pt-6 border-t">
                            <Button variant="outline" as-child>
                                <Link :href="route('admin.threshold-settings.index')">
                                    Batal
                                </Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Threshold' }}
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
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Checkbox } from '@/Components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'

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