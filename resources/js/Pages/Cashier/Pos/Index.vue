<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Select from 'primevue/select';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({
    menuItems:  Array,
    categories: Array,
    tables:     Array,
    shift:      Object,
});

const toast = useToast();
const page  = usePage();

// Flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: flash.success, life: 3000 });
    if (flash?.error)   toast.add({ severity: 'error',   summary: flash.error,   life: 4000 });
}, { immediate: true });

// State
const selectedTable     = ref(null);
const selectedCategory  = ref(null);
const cart              = ref([]);
const notes             = ref('');
const submitting        = ref(false);

// Filter items by selected category
const filteredItems = computed(() => {
    if (! selectedCategory.value) return props.menuItems;
    return props.menuItems.filter(i => i.category_id === selectedCategory.value);
});

const cartTotal = computed(() =>
    cart.value.reduce((sum, i) => sum + i.price * i.qty, 0)
);

const cartQty = computed(() => cart.value.reduce((s, i) => s + i.qty, 0));

function getCartItem(id) {
    return cart.value.find(i => i.id === id) ?? null;
}

function addItem(menuItem) {
    const existing = getCartItem(menuItem.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.value.push({ ...menuItem, qty: 1 });
    }
}

function setQty(id, qty) {
    const item = getCartItem(id);
    if (! item) return;
    if (qty <= 0) {
        cart.value = cart.value.filter(i => i.id !== id);
    } else {
        item.qty = qty;
    }
}

function clearCart() {
    cart.value  = [];
    notes.value = '';
}

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}

function submitOrder() {
    if (! selectedTable.value) {
        toast.add({ severity: 'warn', summary: 'Pilih meja terlebih dahulu.', life: 3000 });
        return;
    }
    if (cart.value.length === 0) {
        toast.add({ severity: 'warn', summary: 'Keranjang masih kosong.', life: 3000 });
        return;
    }

    submitting.value = true;
    router.post(route('cashier.pos.store'), {
        table_id: selectedTable.value,
        items:    cart.value.map(i => ({ id: i.id, qty: i.qty })),
        notes:    notes.value || null,
    }, {
        onSuccess: () => { clearCart(); submitting.value = false; },
        onError:   () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head title="POS — Kasir" />
    <Toast />

    <div class="flex flex-col gap-4">

        <!-- Header bar -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Point of Sale</h1>
                <p class="text-sm text-slate-500 mt-0.5">Input order manual di kasir</p>
            </div>
            <div class="flex items-center gap-3">
                <Tag
                    v-if="shift"
                    :value="`Shift aktif sejak ${shift.started_at}`"
                    severity="success"
                    class="text-xs"
                />
                <Tag v-else value="Belum ada shift aktif" severity="warn" class="text-xs" />

                <Button
                    v-if="!shift"
                    label="Mulai Shift"
                    size="small"
                    severity="success"
                    icon="pi pi-play"
                    @click="router.post(route('cashier.shift.start'))"
                />
                <Button
                    v-else
                    label="Tutup Shift"
                    size="small"
                    severity="secondary"
                    icon="pi pi-stop"
                    @click="router.post(route('cashier.shift.end'))"
                />
            </div>
        </div>

        <!-- Main 2-col layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <!-- Left: Menu -->
            <div class="lg:col-span-2 space-y-4">

                <!-- Category tabs -->
                <div class="flex gap-2 flex-wrap">
                    <button
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors"
                        :class="selectedCategory === null
                            ? 'bg-amber-500 text-white shadow-sm'
                            : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300'"
                        @click="selectedCategory = null"
                    >
                        Semua
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors"
                        :class="selectedCategory === cat.id
                            ? 'bg-amber-500 text-white shadow-sm'
                            : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300'"
                        @click="selectedCategory = cat.id"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Menu grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                    <button
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden text-left transition-all hover:border-amber-300 hover:shadow-md active:scale-95"
                        @click="addItem(item)"
                    >
                        <div class="aspect-square bg-slate-50 overflow-hidden relative">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <i class="pi pi-image text-3xl text-slate-300" />
                            </div>
                            <!-- badge qty in cart -->
                            <div
                                v-if="getCartItem(item.id)"
                                class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center"
                            >
                                {{ getCartItem(item.id).qty }}
                            </div>
                        </div>
                        <div class="p-2.5">
                            <p class="text-sm font-semibold text-slate-800 leading-snug">{{ item.name }}</p>
                            <p class="text-xs text-amber-600 font-medium mt-0.5">{{ formatPrice(item.price) }}</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Right: Order panel -->
            <div class="space-y-4">

                <!-- Table selector -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pilih Meja</label>
                    <Select
                        v-model="selectedTable"
                        :options="tables"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Meja..."
                        class="w-full"
                        :pt="{ root: { class: 'w-full' } }"
                    >
                        <template #option="{ option }">
                            Meja {{ option.number }} — {{ option.name }}
                        </template>
                        <template #value="{ value }">
                            <span v-if="value">
                                Meja {{ tables.find(t => t.id === value)?.number }} — {{ tables.find(t => t.id === value)?.name }}
                            </span>
                            <span v-else class="text-slate-400">Pilih meja...</span>
                        </template>
                    </Select>
                </div>

                <!-- Cart items -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Keranjang
                            <span v-if="cartQty" class="ml-1 text-amber-500">({{ cartQty }})</span>
                        </p>
                        <button
                            v-if="cart.length"
                            class="text-xs text-slate-400 hover:text-red-500 transition-colors"
                            @click="clearCart"
                        >
                            Kosongkan
                        </button>
                    </div>

                    <div v-if="!cart.length" class="px-4 py-8 text-center text-slate-400 text-sm">
                        Klik menu untuk menambah item
                    </div>

                    <div v-else class="divide-y divide-slate-50 max-h-64 overflow-y-auto">
                        <div v-for="item in cart" :key="item.id" class="px-4 py-3 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ item.name }}</p>
                                <p class="text-xs text-slate-400">{{ formatPrice(item.price) }}</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button
                                    class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-sm flex items-center justify-center hover:bg-red-50 hover:text-red-500"
                                    @click="setQty(item.id, item.qty - 1)"
                                >
                                    <i class="pi pi-minus text-xs" />
                                </button>
                                <span class="text-sm font-semibold w-5 text-center">{{ item.qty }}</span>
                                <button
                                    class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 text-sm flex items-center justify-center hover:bg-amber-200"
                                    @click="setQty(item.id, item.qty + 1)"
                                >
                                    <i class="pi pi-plus text-xs" />
                                </button>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 w-20 text-right shrink-0">
                                {{ formatPrice(item.price * item.qty) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Catatan</label>
                    <Textarea
                        v-model="notes"
                        rows="2"
                        placeholder="Catatan order (opsional)..."
                        class="w-full text-sm"
                    />
                </div>

                <!-- Total + Submit -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 font-medium">Total</span>
                        <span class="text-xl font-black text-slate-800">{{ formatPrice(cartTotal) }}</span>
                    </div>
                    <Button
                        label="Buat Order"
                        icon="pi pi-check"
                        class="w-full"
                        :loading="submitting"
                        :disabled="!selectedTable || cart.length === 0"
                        @click="submitOrder"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
