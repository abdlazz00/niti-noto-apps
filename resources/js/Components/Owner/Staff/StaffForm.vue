<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';

const props = defineProps({
    form: Object,
    isEdit: { type: Boolean, default: false },
    initialPhotoUrl: { type: String, default: null },
});

const emit = defineEmits(['submit']);

const roleOptions = [
    { label: 'Owner', value: 'owner' },
    { label: 'Kasir', value: 'cashier' },
    { label: 'Staff', value: 'staff' },
];

const photoPreview = ref(props.initialPhotoUrl);
const photoInput = ref(null);

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    props.form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
}
</script>

<template>
    <div class="space-y-5">
        <!-- Foto Upload -->
        <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-amber-100 overflow-hidden flex items-center justify-center shrink-0">
                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="preview" />
                <i v-else class="pi pi-user text-amber-400 text-2xl" />
            </div>
            <div>
                <Button
                    type="button"
                    :label="isEdit ? 'Ganti Foto' : 'Upload Foto'"
                    icon="pi pi-upload"
                    outlined severity="secondary" size="small"
                    @click="photoInput.click()"
                />
                <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, maks. 2MB</p>
                <small v-if="form.errors.photo" class="text-red-500">{{ form.errors.photo }}</small>
            </div>
        </div>

        <!-- Data Akun -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <InputText v-model="form.name" class="w-full" :class="{ 'p-invalid': form.errors.name }" placeholder="Nama lengkap" />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <InputText v-model="form.email" type="email" class="w-full" :class="{ 'p-invalid': form.errors.email }" placeholder="email@warung.com" />
                <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Role <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.role"
                    :options="roleOptions" optionLabel="label" optionValue="value"
                    placeholder="Pilih role" class="w-full" :class="{ 'p-invalid': form.errors.role }"
                />
                <small v-if="form.errors.role" class="text-red-500">{{ form.errors.role }}</small>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Password
                    <span v-if="!isEdit" class="text-red-500">*</span>
                    <span v-else class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span>
                </label>
                <Password
                    v-model="form.password"
                    placeholder="Min. 8 karakter"
                    class="w-full" :feedback="false" toggleMask
                    :class="{ 'p-invalid': form.errors.password }"
                    pt:root:class="w-full" pt:pcinputtext:root:class="w-full"
                />
                <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
            </div>
        </div>

        <!-- Info Tambahan -->
        <div class="border-t border-slate-100 pt-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">Info Tambahan</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label>
                    <InputText v-model="form.phone" class="w-full" placeholder="08xx-xxxx-xxxx" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Bergabung</label>
                    <DatePicker
                        v-model="form.join_date"
                        class="w-full" dateFormat="dd/mm/yy" placeholder="Pilih tanggal"
                        pt:root:class="w-full"
                    />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                    <InputText v-model="form.address" class="w-full" placeholder="Alamat lengkap" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                    <Textarea v-model="form.notes" class="w-full" rows="3" placeholder="Catatan tambahan..." autoResize />
                </div>
            </div>
        </div>
    </div>
</template>
