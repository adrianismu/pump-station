<script setup>
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <div class="w-full lg:grid lg:min-h-screen lg:grid-cols-2">
        <div class="flex items-center justify-center py-12">
            <div class="mx-auto grid w-[350px] gap-6">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">Reset Password</h1>
                    <p class="text-balance text-muted-foreground">
                        Masukkan password baru untuk akun Anda
                    </p>
                </div>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            readonly
                            class="bg-gray-50"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password Baru</Label>
                        <Input
                            id="password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            v-model="form.password"
                            required
                            autofocus
                            autocomplete="new-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Konfirmasi Password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            placeholder="Ulangi password baru"
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
                        {{ form.processing ? 'Memproses...' : 'Reset Password' }}
                    </Button>
                </form>
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
                            <h2 class="text-4xl font-bold mb-4 drop-shadow-lg">Password Baru</h2>
                            <p class="text-xl mb-8 drop-shadow-md">Buat password yang aman untuk akun Anda</p>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-6 max-w-md border border-white/30">
                                <h3 class="text-lg font-semibold mb-2">Tips Password Aman:</h3>
                                <ul class="text-left space-y-2">
                                    <li>• Minimal 8 karakter</li>
                                    <li>• Kombinasi huruf besar dan kecil</li>
                                    <li>• Gunakan angka dan simbol</li>
                                    <li>• Hindari informasi pribadi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
