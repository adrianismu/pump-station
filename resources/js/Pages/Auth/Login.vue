<script setup>
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="w-full lg:grid lg:min-h-screen lg:grid-cols-2">
        <div class="flex items-center justify-center py-12">
            <div class="mx-auto grid w-[350px] gap-6">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">Login</h1>
                    <p class="text-balance text-muted-foreground">
                        Masukkan email dan password untuk mengakses akun Anda
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

                    <div class="grid gap-2">
                        <div class="flex items-center">
                            <Label for="password">Password</Label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="ml-auto inline-block text-sm underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Lupa password?
                            </Link>
                        </div>
                        <Input 
                            id="password" 
                            type="password" 
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox 
                            id="remember" 
                            name="remember" 
                            v-model:checked="form.remember" 
                        />
                        <Label 
                            for="remember" 
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Ingat saya
                        </Label>
                    </div>

                    <Button 
                        type="submit" 
                        class="w-full" 
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </Button>
                </form>

                <div class="mt-4 text-center text-sm">
                    Belum punya akun?
                    <Link :href="route('register')" class="underline hover:text-gray-900">
                        Daftar sekarang
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
