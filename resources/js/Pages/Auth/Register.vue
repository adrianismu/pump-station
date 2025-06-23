<script setup>
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="w-full lg:grid lg:min-h-screen lg:grid-cols-2">
        <div class="flex items-center justify-center py-12">
            <div class="mx-auto grid w-[350px] gap-6">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">Daftar Akun</h1>
                    <p class="text-balance text-muted-foreground">
                        Buat akun baru untuk mengakses sistem monitoring
                    </p>
                </div>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input
                            id="name"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="nama@contoh.com"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Konfirmasi Password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            placeholder="Ulangi password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                    </div>

                    <Button 
                        type="submit" 
                        class="w-full" 
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
                    </Button>
                </form>

                <div class="mt-4 text-center text-sm">
                    Sudah punya akun?
                    <Link :href="route('login')" class="underline hover:text-gray-900">
                        Masuk di sini
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
