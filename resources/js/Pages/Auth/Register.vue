<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

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
    <Head title="Daftar" />

    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo / Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500 mb-4">
                    <span class="text-white text-2xl font-black">NN</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Niti Noto</h1>
                <p class="text-slate-500 text-sm mt-1">Buat akun untuk melacak pesananmu</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Daftar Akun</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                        <InputText
                            v-model="form.name"
                            type="text"
                            placeholder="Nama kamu"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.name }"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <small v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <InputText
                            v-model="form.email"
                            type="email"
                            placeholder="nama@email.com"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.email }"
                            required
                            autocomplete="username"
                        />
                        <small v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <Password
                            v-model="form.password"
                            placeholder="Min. 8 karakter"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.password }"
                            toggleMask
                            required
                            autocomplete="new-password"
                            pt:root:class="w-full"
                            pt:pcinputtext:root:class="w-full"
                        />
                        <small v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                        <Password
                            v-model="form.password_confirmation"
                            placeholder="Ulangi password"
                            class="w-full"
                            :feedback="false"
                            toggleMask
                            required
                            autocomplete="new-password"
                            pt:root:class="w-full"
                            pt:pcinputtext:root:class="w-full"
                        />
                        <small v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</small>
                    </div>

                    <Button
                        type="submit"
                        label="Daftar"
                        class="w-full"
                        :loading="form.processing"
                        severity="warn"
                        size="large"
                    />

                    <p class="text-center text-sm text-slate-500">
                        Sudah punya akun?
                        <Link :href="route('login')" class="text-amber-600 hover:text-amber-700 font-medium">Masuk</Link>
                    </p>
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Niti Noto
            </p>
        </div>
    </div>
</template>
