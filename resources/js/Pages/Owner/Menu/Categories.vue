<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryManager from '@/Components/Owner/Menu/CategoryManager.vue';
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

defineProps({ categories: Array });

const page = usePage();
const toast = useToast();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.add({ severity: 'success', summary: 'Berhasil', detail: flash.success, life: 3000 });
        if (flash?.error) toast.add({ severity: 'error', summary: 'Gagal', detail: flash.error, life: 4000 });
    },
    { deep: true }
);
</script>

<template>
    <Head title="Kategori Menu" />
    <Toast />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Kategori Menu</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola pengelompokan menu warung</p>
            </div>
            <Link :href="route('owner.menu-items.index')">
                <Button label="Lihat Menu Items" icon="pi pi-arrow-right" iconPos="right" outlined severity="secondary" />
            </Link>
        </div>

        <CategoryManager :categories="categories" />
    </div>
</template>
