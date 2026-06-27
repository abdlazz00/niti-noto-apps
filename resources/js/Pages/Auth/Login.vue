<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

defineProps({
    canResetPassword: Boolean,
    status: String,
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
    <Head title="Masuk" />

    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo / Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500 mb-4">
                    <span class="text-white text-2xl font-black">NN</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Niti Noto</h1>
                <p class="text-slate-500 text-sm mt-1">Sistem Informasi Warung Kopi</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Masuk ke Akun</h2>

                <Message v-if="status" severity="success" class="mb-4">{{ status }}</Message>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <InputText
                            v-model="form.email"
                            type="email"
                            placeholder="nama@email.com"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.email }"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <small v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <Password
                            v-model="form.password"
                            placeholder="Password"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.password }"
                            :feedback="false"
                            toggleMask
                            required
                            autocomplete="current-password"
                            pt:root:class="w-full"
                            pt:pcinputtext:root:class="w-full"
                        />
                        <small v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</small>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.remember" inputId="remember" :binary="true" />
                            <label for="remember" class="text-sm text-slate-600 cursor-pointer">Ingat saya</label>
                        </div>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-amber-600 hover:text-amber-700 font-medium"
                        >
                            Lupa password?
                        </Link>
                    </div>

                    <Button
                        type="submit"
                        label="Masuk"
                        class="w-full"
                        :loading="form.processing"
                        severity="warn"
                        size="large"
                    />
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Niti Noto
            </p>
        </div>
    </div>
</template>
