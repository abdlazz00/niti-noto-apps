<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StaffForm from '@/Components/Owner/Staff/StaffForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineOptions({ layout: AppLayout });

const props = defineProps({ staff: Object });

const form = useForm({
    name:       props.staff.name,
    email:      props.staff.email,
    password:   '',
    role:       props.staff.role,
    photo:      null,
    phone:      props.staff.phone ?? '',
    address:    props.staff.address ?? '',
    join_date:  props.staff.join_date ? new Date(props.staff.join_date) : null,
    notes:      props.staff.notes ?? '',
    _method:    'PUT',
});

function submit() {
    form.post(route('owner.staff.update', props.staff.id), { forceFormData: true });
}
</script>

<template>
    <Head title="Edit Staff" />

    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('owner.staff.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Staff</h1>
                <p class="text-slate-500 text-sm">Perbarui data {{ staff.name }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <StaffForm
                :form="form"
                :is-edit="true"
                :initial-photo-url="staff.photo ? `/storage/${staff.photo}` : null"
            />

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <Link :href="route('owner.staff.index')">
                    <Button type="button" label="Batal" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Perubahan" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
