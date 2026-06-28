<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import ToggleSwitch from 'primevue/toggleswitch';
import Button from 'primevue/button';

const props = defineProps({
    form: Object,
    categories: Array,
    isEdit: { type: Boolean, default: false },
    initialImageUrl: { type: String, default: null },
});

const categoryOptions = props.categories.map(c => ({ label: c.name, value: c.id }));

const imagePreview = ref(props.initialImageUrl);
const imageInput = ref(null);

function onImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    props.form.image = file;
    imagePreview.value = URL.createObjectURL(file);
}
</script>

<template>
    <div class="space-y-5">
        <!-- Foto Upload -->
        <div class="flex items-start gap-4">
            <div class="w-24 h-24 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center shrink-0 border border-slate-200">
                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" alt="preview" />
                <i v-else class="pi pi-image text-slate-300 text-3xl" />
            </div>
            <div class="pt-1">
                <Button
                    type="button"
                    :label="isEdit ? 'Ganti Foto' : 'Upload Foto'"
                    icon="pi pi-upload"
                    outlined severity="secondary" size="small"
                    @click="imageInput.click()"
                />
                <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onImageChange" />
                <p class="text-xs text-slate-400 mt-1.5">JPG, PNG, maks. 2MB</p>
                <small v-if="form.errors.image" class="text-red-500">{{ form.errors.image }}</small>
            </div>
        </div>

        <!-- Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Menu <span class="text-red-500">*</span>
                </label>
                <InputText
                    v-model="form.name"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.name }"
                    placeholder="Contoh: Es Kopi Susu, Nasi Goreng..."
                />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.category_id"
                    :options="categoryOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Pilih kategori"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.category_id }"
                />
                <small v-if="form.errors.category_id" class="text-red-500">{{ form.errors.category_id }}</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Harga <span class="text-red-500">*</span>
                </label>
                <InputNumber
                    v-model="form.price"
                    class="w-full"
                    :class="{ 'p-invalid': form.errors.price }"
                    prefix="Rp "
                    :min="0"
                    :useGrouping="true"
                    placeholder="0"
                    pt:root:class="w-full"
                    pt:pcinputtext:root:class="w-full"
                />
                <small v-if="form.errors.price" class="text-red-500">{{ form.errors.price }}</small>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                <div class="flex items-center gap-3">
                    <ToggleSwitch v-model="form.is_active" />
                    <span class="text-sm" :class="form.is_active ? 'text-emerald-600' : 'text-slate-400'">
                        {{ form.is_active ? 'Aktif (tampil di menu)' : 'Nonaktif (disembunyikan)' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
