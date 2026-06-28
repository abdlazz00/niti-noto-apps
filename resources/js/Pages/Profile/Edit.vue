<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const isOwner = computed(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles.some(r => r.name === 'owner');
});
</script>

<template>
    <Head title="Profil Saya" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data profil dan keamanan akun Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <UpdatePasswordForm />
            </div>
        </div>

        <div
            v-if="isOwner"
            class="bg-white p-6 rounded-2xl border border-red-100 bg-red-50/20 shadow-sm max-w-xl"
        >
            <DeleteUserForm />
        </div>
    </div>
</template>
