<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({
    expenses:   Array,
    categories: Array,
});

const toast = useToast();
const page  = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: flash.success, life: 3000 });
    if (flash?.error)   toast.add({ severity: 'error',   summary: flash.error,   life: 4000 });
}, { immediate: true });

const form = useForm({
    title:       '',
    amount:      null,
    category_id: null,
    attachment:  null,
});

const fileInput    = ref(null);
const previewUrl   = ref(null);

function onFileChange(e) {
    const file = e.target.files[0] ?? null;
    form.attachment = file;
    previewUrl.value = file ? URL.createObjectURL(file) : null;
}

function submitForm() {
    form.post(route('cashier.expenses.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            previewUrl.value = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

const statusConfig = {
    pending:  { label: 'Pending',   severity: 'warn'    },
    approved: { label: 'Disetujui', severity: 'success' },
    rejected: { label: 'Ditolak',   severity: 'danger'  },
};
</script>

<template>
    <Head title="Expense — Kasir" />
    <Toast />

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengajuan Expense</h1>
            <p class="text-sm text-slate-500 mt-0.5">Ajukan pengeluaran operasional untuk disetujui owner</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-4">Form Pengajuan Baru</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Title -->
                <div class="md:col-span-2 space-y-1">
                    <label class="text-xs font-medium text-slate-500">Judul Expense <span class="text-red-400">*</span></label>
                    <InputText v-model="form.title" placeholder="Contoh: Beli Kopi Arabica" class="w-full" :invalid="!!form.errors.title" />
                    <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                </div>

                <!-- Amount -->
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-500">Jumlah <span class="text-red-400">*</span></label>
                    <InputNumber
                        v-model="form.amount"
                        prefix="Rp "
                        :useGrouping="true"
                        placeholder="0"
                        class="w-full"
                        :invalid="!!form.errors.amount"
                    />
                    <p v-if="form.errors.amount" class="text-xs text-red-500">{{ form.errors.amount }}</p>
                </div>

                <!-- Category -->
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-500">Kategori <span class="text-red-400">*</span></label>
                    <Select
                        v-model="form.category_id"
                        :options="categories"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Pilih kategori..."
                        class="w-full"
                        :invalid="!!form.errors.category_id"
                    />
                    <p v-if="form.errors.category_id" class="text-xs text-red-500">{{ form.errors.category_id }}</p>
                </div>

                <!-- Attachment -->
                <div class="md:col-span-2 space-y-1">
                    <label class="text-xs font-medium text-slate-500">Foto Bukti (opsional)</label>
                    <div class="flex items-start gap-4">
                        <div
                            v-if="previewUrl"
                            class="w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shrink-0 cursor-pointer"
                            @click="$refs.lightbox.showModal()"
                        >
                            <img :src="previewUrl" class="w-full h-full object-cover" />
                        </div>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer"
                            @change="onFileChange"
                        />
                    </div>
                    <p v-if="form.errors.attachment" class="text-xs text-red-500">{{ form.errors.attachment }}</p>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <Button
                    label="Ajukan Expense"
                    icon="pi pi-send"
                    :loading="form.processing"
                    :disabled="!form.title || !form.amount || !form.category_id"
                    @click="submitForm"
                />
            </div>
        </div>

        <!-- Expense list -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50">
                <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Riwayat Pengajuan Saya</h2>
            </div>

            <div v-if="!expenses.length" class="p-12 text-center text-slate-400">
                <i class="pi pi-file text-4xl mb-3 block" />
                <p class="text-sm">Belum ada pengajuan expense</p>
            </div>

            <div v-else class="divide-y divide-slate-50">
                <div v-for="expense in expenses" :key="expense.id" class="px-6 py-4 flex items-start gap-4">
                    <!-- Attachment thumbnail -->
                    <div class="w-12 h-12 rounded-xl shrink-0 overflow-hidden border border-slate-100 bg-slate-50 flex items-center justify-center">
                        <img
                            v-if="expense.attachment"
                            :src="expense.attachment"
                            class="w-full h-full object-cover cursor-pointer"
                            @click="window.open(expense.attachment)"
                        />
                        <i v-else class="pi pi-file-o text-slate-300 text-xl" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 text-sm truncate">{{ expense.title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ expense.category_name }} · {{ expense.created_at }}
                        </p>
                        <p v-if="expense.notes && expense.status === 'rejected'" class="text-xs text-red-500 mt-1 italic">
                            Alasan ditolak: {{ expense.notes }}
                        </p>
                    </div>

                    <!-- Amount + Status -->
                    <div class="text-right shrink-0">
                        <p class="font-bold text-slate-800 text-sm">{{ formatPrice(expense.amount) }}</p>
                        <Tag
                            :value="statusConfig[expense.status]?.label"
                            :severity="statusConfig[expense.status]?.severity"
                            class="text-xs mt-1"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
