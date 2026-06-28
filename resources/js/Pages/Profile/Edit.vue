<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

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

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <Tabs value="0">
                <TabList>
                    <Tab value="0" class="flex items-center gap-2">
                        <i class="pi pi-user text-sm" />
                        <span>Informasi Profil</span>
                    </Tab>
                    <Tab value="1" class="flex items-center gap-2">
                        <i class="pi pi-lock text-sm" />
                        <span>Keamanan Akun</span>
                    </Tab>
                    <Tab v-if="isOwner" value="2" class="flex items-center gap-2 text-red-600">
                        <i class="pi pi-trash text-sm" />
                        <span>Hapus Akun</span>
                    </Tab>
                </TabList>
                <TabPanels class="p-6">
                    <TabPanel value="0">
                        <div class="max-w-xl">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                    </TabPanel>
                    <TabPanel value="1">
                        <div class="max-w-xl">
                            <UpdatePasswordForm />
                        </div>
                    </TabPanel>
                    <TabPanel v-if="isOwner" value="2">
                        <div class="max-w-xl">
                            <DeleteUserForm />
                        </div>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </div>
</template>
