<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({
    expenses:   Array,
    categories: Array,
    filters:    Object,
});

const toast = useToast();
const page  = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: flash.success, life: 3000 });
    if (flash?.error)   toast.add({ severity: 'error',   summary: flash.error,   life: 4000 });
}, { immediate: true });

// Filters
const activeStatus   = ref(props.filters?.status ?? '');
const categoryFilter = ref(props.filters?.category_id ?? null);

function applyFilters() {
    router.get(route('owner.expenses'), {
        status:      activeStatus.value || undefined,
        category_id: categoryFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

watch([activeStatus, categoryFilter], applyFilters);

// Reject dialog
const showRejectDialog   = ref(false);
const rejectingExpense   = ref(null);
const rejectForm         = useForm({ notes: '' });

function openReject(expense) {
    rejectingExpense.value = expense;
    rejectForm.reset();
    showRejectDialog.value = true;
}

function submitReject() {
    rejectForm.patch(route('owner.expenses.reject', rejectingExpense.value.id), {
        onSuccess: () => { showRejectDialog.value = false; },
    });
}

function approveExpense(expense) {
    router.patch(route('owner.expenses.approve', expense.id), {}, { preserveScroll: true });
}

// Lightbox
const lightboxSrc     = ref(null);
const lightboxVisible = computed({
    get: () => !!lightboxSrc.value,
    set: (v) => { if (!v) lightboxSrc.value = null; },
});

// Category management dialog
const showCatDialog = ref(false);
const catForm       = useForm({ name: '' });
const editingCat    = ref(null);

function openAddCat() {
    editingCat.value = null;
    catForm.reset();
    showCatDialog.value = true;
}

function openEditCat(cat) {
    editingCat.value = cat;
    catForm.name = cat.name;
    showCatDialog.value = true;
}

function submitCat() {
    if (editingCat.value) {
        catForm.patch(route('owner.expense-categories.update', editingCat.value.id), {
            onSuccess: () => { showCatDialog.value = false; },
        });
    } else {
        catForm.post(route('owner.expense-categories.store'), {
            onSuccess: () => { showCatDialog.value = false; },
        });
    }
}

function deleteCat(cat) {
    router.delete(route('owner.expense-categories.destroy', cat.id), { preserveScroll: true });
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

const statusConfig = {
    '':       { label: 'Semua',     severity: null       },
    pending:  { label: 'Pending',   severity: 'warn'     },
    approved: { label: 'Disetujui', severity: 'success'  },
    rejected: { label: 'Ditolak',   severity: 'danger'   },
};

const statusTabs = ['', 'pending', 'approved', 'rejected'];
</script>

<template>
    <Head title="Expense — Owner" />
    <Toast />

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Expense</h1>
                <p class="text-sm text-slate-500 mt-0.5">Review dan setujui pengajuan pengeluaran</p>
            </div>
            <Button
                label="Kelola Kategori"
                icon="pi pi-tags"
                severity="secondary"
                size="small"
                @click="showCatDialog = true"
            />
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Status tabs -->
            <div class="flex gap-1.5">
                <button
                    v-for="status in statusTabs"
                    :key="status"
                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors"
                    :class="activeStatus === status
                        ? 'bg-amber-500 text-white'
                        : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300'"
                    @click="activeStatus = status"
                >
                    {{ statusConfig[status]?.label }}
                </button>
            </div>

            <!-- Category filter -->
            <Select
                v-model="categoryFilter"
                :options="[{ id: null, name: 'Semua Kategori' }, ...categories]"
                optionLabel="name"
                optionValue="id"
                class="text-sm"
                style="min-width: 160px"
            />
        </div>

        <!-- Expense list -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div v-if="!expenses.length" class="p-12 text-center text-slate-400">
                <i class="pi pi-inbox text-4xl mb-3 block" />
                <p class="text-sm">Tidak ada expense ditemukan</p>
            </div>

            <div v-else class="divide-y divide-slate-50">
                <div v-for="expense in expenses" :key="expense.id" class="px-6 py-4 flex items-start gap-4">
                    <!-- Attachment thumbnail -->
                    <div
                        class="w-14 h-14 rounded-xl shrink-0 overflow-hidden border border-slate-100 bg-slate-50 flex items-center justify-center cursor-pointer"
                        @click="expense.attachment && (lightboxSrc = expense.attachment)"
                    >
                        <img v-if="expense.attachment" :src="expense.attachment" class="w-full h-full object-cover" />
                        <i v-else class="pi pi-file-o text-slate-300 text-xl" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-slate-800 text-sm">{{ expense.title }}</p>
                            <Tag
                                :value="statusConfig[expense.status]?.label"
                                :severity="statusConfig[expense.status]?.severity"
                                class="text-xs"
                            />
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ expense.category_name }} · Diajukan oleh {{ expense.submitted_by }} · {{ expense.created_at }}
                        </p>
                        <p v-if="expense.notes && expense.status === 'rejected'" class="text-xs text-red-500 mt-1 italic">
                            Alasan: {{ expense.notes }}
                        </p>
                        <p v-if="expense.status === 'approved'" class="text-xs text-green-600 mt-1">
                            Disetujui oleh {{ expense.approved_by }} · {{ expense.approved_at }}
                        </p>
                    </div>

                    <!-- Amount + Actions -->
                    <div class="text-right shrink-0 space-y-2">
                        <p class="font-bold text-slate-800">{{ formatPrice(expense.amount) }}</p>
                        <div v-if="expense.status === 'pending'" class="flex gap-1.5 justify-end">
                            <Button
                                label="Setujui"
                                icon="pi pi-check"
                                size="small"
                                severity="success"
                                @click="approveExpense(expense)"
                            />
                            <Button
                                label="Tolak"
                                icon="pi pi-times"
                                size="small"
                                severity="danger"
                                outlined
                                @click="openReject(expense)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject dialog -->
    <Dialog v-model:visible="showRejectDialog" modal header="Tolak Expense" style="width: 420px">
        <div v-if="rejectingExpense" class="space-y-4">
            <p class="text-sm text-slate-600">
                Masukkan alasan penolakan untuk <strong>{{ rejectingExpense.title }}</strong>.
            </p>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Alasan Penolakan <span class="text-red-400">*</span></label>
                <Textarea
                    v-model="rejectForm.notes"
                    rows="3"
                    placeholder="Tuliskan alasan penolakan..."
                    class="w-full"
                    :invalid="!!rejectForm.errors.notes"
                />
                <p v-if="rejectForm.errors.notes" class="text-xs text-red-500">{{ rejectForm.errors.notes }}</p>
            </div>
        </div>
        <template #footer>
            <Button label="Batal" severity="secondary" outlined @click="showRejectDialog = false" />
            <Button
                label="Tolak Expense"
                severity="danger"
                icon="pi pi-times"
                :loading="rejectForm.processing"
                :disabled="!rejectForm.notes"
                @click="submitReject"
            />
        </template>
    </Dialog>

    <!-- Category management dialog -->
    <Dialog v-model:visible="showCatDialog" modal header="Kelola Kategori Expense" style="width: 480px">
        <div class="space-y-4">
            <!-- Add/Edit form -->
            <div class="flex gap-2">
                <InputText v-model="catForm.name" placeholder="Nama kategori..." class="flex-1" :invalid="!!catForm.errors.name" />
                <Button
                    :label="editingCat ? 'Update' : 'Tambah'"
                    icon="pi pi-plus"
                    :loading="catForm.processing"
                    :disabled="!catForm.name"
                    @click="submitCat"
                />
                <Button v-if="editingCat" label="Batal" severity="secondary" outlined @click="editingCat = null; catForm.reset()" />
            </div>
            <p v-if="catForm.errors.name" class="text-xs text-red-500">{{ catForm.errors.name }}</p>

            <!-- Category list -->
            <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                <div v-if="!categories.length" class="p-4 text-sm text-slate-400 text-center">Belum ada kategori</div>
                <div v-for="cat in categories" :key="cat.id" class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm text-slate-700">{{ cat.name }}</span>
                    <div class="flex gap-1">
                        <Button icon="pi pi-pencil" text size="small" @click="openEditCat(cat)" />
                        <Button icon="pi pi-trash" text size="small" severity="danger" @click="deleteCat(cat)" />
                    </div>
                </div>
            </div>
        </div>
    </Dialog>

    <!-- Lightbox -->
    <Dialog v-model:visible="lightboxVisible" modal :header="null" style="max-width: 90vw">
        <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl" />
    </Dialog>
</template>
