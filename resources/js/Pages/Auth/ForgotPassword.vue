<script setup>
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="w-full lg:grid lg:min-h-screen lg:grid-cols-2">
        <div class="flex items-center justify-center py-12">
            <div class="mx-auto grid w-[350px] gap-6">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">Lupa Password</h1>
                    <p class="text-balance text-muted-foreground">
                        Masukkan email Anda dan kami akan mengirim link reset password
                    </p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="admin@pumpstation.com"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <Button 
                        type="submit" 
                        class="w-full" 
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Mengirim...' : 'Kirim Link Reset' }}
                    </Button>
                </form>

                <div class="mt-4 text-center text-sm">
                    Kembali ke halaman
                    <Link :href="route('login')" class="underline hover:text-gray-900">
                        Login
                    </Link>
                </div>
            </div>
        </div>
        
        <div class="hidden bg-muted lg:block relative">
            <div 
                class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('https://c1.wallpaperflare.com/preview/944/749/136/surabaya-bridge-suramadu-sky-java-madura.jpg')"
            >
                <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]">
                    <div class="flex items-center justify-center h-full p-8">
                        <div class="text-center text-white">
                            <h2 class="text-4xl font-bold mb-4 drop-shadow-lg">Reset Password</h2>
                            <p class="text-xl mb-8 drop-shadow-md">Kembalikan akses ke akun Anda dengan mudah</p>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-6 max-w-md border border-white/30">
                                <h3 class="text-lg font-semibold mb-2">Langkah Reset:</h3>
                                <ul class="text-left space-y-2">
                                    <li>• Masukkan email yang terdaftar</li>
                                    <li>• Periksa email untuk link reset</li>
                                    <li>• Klik link dan buat password baru</li>
                                    <li>• Login dengan password yang baru</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
