<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';

defineOptions({ layout: AppLayout });

const props = defineProps({
    staff: Object,
});

const roleOptions = [
    { label: 'Owner', value: 'owner' },
    { label: 'Kasir', value: 'cashier' },
    { label: 'Staff', value: 'staff' },
];

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    password: '',
    role: props.staff.role,
    photo: null,
    phone: props.staff.phone ?? '',
    address: props.staff.address ?? '',
    join_date: props.staff.join_date ? new Date(props.staff.join_date) : null,
    notes: props.staff.notes ?? '',
    _method: 'PUT',
});

const photoPreview = ref(props.staff.photo ? `/storage/${props.staff.photo}` : null);

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post(route('owner.staff.update', props.staff.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Edit Staff" />

    <div class="max-w-2xl space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('owner.staff.index')">
                <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Staff</h1>
                <p class="text-slate-500 text-sm">Perbarui data {{ staff.name }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">

            <!-- Foto -->
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-amber-100 overflow-hidden flex items-center justify-center shrink-0">
                    <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="preview" />
                    <span v-else class="text-amber-700 font-bold text-2xl">{{ staff.name.charAt(0).toUpperCase() }}</span>
                </div>
                <div>
                    <Button type="button" label="Ganti Foto" icon="pi pi-upload" outlined severity="secondary" size="small"
                        @click="$refs.photoInput.click()" />
                    <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, maks. 2MB</p>
                    <small v-if="form.errors.photo" class="text-red-500">{{ form.errors.photo }}</small>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <InputText v-model="form.name" class="w-full" :class="{ 'p-invalid': form.errors.name }" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <InputText v-model="form.email" type="email" class="w-full" :class="{ 'p-invalid': form.errors.email }" />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <Select v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value"
                        class="w-full" :class="{ 'p-invalid': form.errors.role }" />
                    <small v-if="form.errors.role" class="text-red-500">{{ form.errors.role }}</small>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru <span class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                    <Password v-model="form.password" placeholder="Min. 8 karakter" class="w-full" :feedback="false" toggleMask
                        :class="{ 'p-invalid': form.errors.password }"
                        pt:root:class="w-full" pt:pcinputtext:root:class="w-full" />
                    <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">Info Tambahan</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label>
                        <InputText v-model="form.phone" class="w-full" placeholder="08xx-xxxx-xxxx" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Bergabung</label>
                        <DatePicker v-model="form.join_date" class="w-full" dateFormat="dd/mm/yy"
                            pt:root:class="w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                        <InputText v-model="form.address" class="w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                        <Textarea v-model="form.notes" class="w-full" rows="3" autoResize />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <Link :href="route('owner.staff.index')">
                    <Button type="button" label="Batal" outlined severity="secondary" />
                </Link>
                <Button type="submit" label="Simpan Perubahan" icon="pi pi-check" severity="warn" :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
