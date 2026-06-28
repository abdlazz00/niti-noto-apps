<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col transition-all duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarCollapsed ? 'lg:w-[70px]' : 'lg:w-64'
            ]"
        >
            <!-- Logo -->
            <div :class="['flex items-center border-b border-slate-100 transition-all duration-300', sidebarCollapsed ? 'justify-center py-5 px-0' : 'gap-3 px-6 py-5']">
                <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                    <span class="text-white font-bold text-sm">NN</span>
                </div>
                <span v-if="!sidebarCollapsed" class="font-bold text-slate-800 text-lg truncate">Niti Noto</span>
            </div>

            <!-- Navigation -->
            <nav :class="['flex-1 py-4 space-y-1 overflow-y-auto', sidebarCollapsed ? 'px-2' : 'px-4']">
                <template v-for="item in navItems" :key="item.label">
                    <Link
                        :href="item.href"
                        :class="[
                            'flex items-center transition-colors',
                            sidebarCollapsed ? 'lg:justify-center lg:px-0 lg:py-3 rounded-xl' : 'gap-3 px-3 py-2.5 rounded-lg text-sm font-medium',
                            isActive(item.href)
                                ? 'bg-amber-50 text-amber-700'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                        ]"
                        v-tooltip.right="sidebarCollapsed ? item.label : null"
                    >
                        <i :class="['text-base shrink-0', item.icon]" />
                        <span v-if="!sidebarCollapsed" class="truncate">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </aside>

        <!-- Overlay for mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/30 z-40 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main content -->
        <div
            :class="[
                'flex-1 flex flex-col min-h-screen transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-[70px]' : 'lg:ml-64'
            ]"
        >
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between shadow-sm">
                <!-- Left header: Sidebar Toggles & Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <!-- Mobile toggle -->
                    <button
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none"
                        @click="sidebarOpen = !sidebarOpen"
                    >
                        <i class="pi pi-bars text-lg" />
                    </button>
                    <!-- Desktop collapse toggle -->
                    <button
                        class="hidden lg:flex p-2 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                    >
                        <i :class="['pi text-lg', sidebarCollapsed ? 'pi-angle-double-right' : 'pi-angle-double-left']" />
                    </button>

                    <!-- Breadcrumbs -->
                    <nav class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-400">
                        <span class="hover:text-slate-600">Niti Noto</span>
                        <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
                            <i class="pi pi-chevron-right text-[9px] text-slate-300" />
                            <span :class="idx === breadcrumbs.length - 1 ? 'text-amber-600 font-bold' : 'hover:text-slate-600'">
                                {{ crumb }}
                            </span>
                        </template>
                    </nav>
                </div>

                <!-- Right header: Active User Profile Dropdown -->
                <div class="relative">
                    <button
                        @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-3 px-3 py-1.5 rounded-xl border border-slate-100 hover:bg-slate-50/80 transition-all focus:outline-none"
                    >
                        <!-- Avatar img -->
                        <img
                            v-if="$page.props.auth.user.photo"
                            :src="`/storage/${$page.props.auth.user.photo}`"
                            class="w-8 h-8 rounded-full object-cover border border-slate-200"
                            alt="avatar"
                        />
                        <div v-else class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center border border-amber-200">
                            <span class="text-amber-800 font-bold text-sm">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <!-- Name details -->
                        <div class="hidden md:flex flex-col text-left">
                            <span class="text-sm font-semibold text-slate-700 leading-none mb-0.5">{{ $page.props.auth.user.name }}</span>
                            <span class="text-[10px] text-slate-400 font-medium capitalize">{{ userRole }}</span>
                        </div>
                        <i class="pi pi-chevron-down text-[10px] text-slate-400" />
                    </button>

                    <!-- Background click closer -->
                    <div v-if="dropdownOpen" class="fixed inset-0 z-40" @click="dropdownOpen = false"></div>

                    <!-- Dropdown card -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-56 bg-white border border-slate-100 rounded-2xl shadow-xl py-2 z-50 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/50">
                                <p class="text-xs text-slate-400 font-medium">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate mt-0.5">{{ $page.props.auth.user.name }}</p>
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <Link
                                href="/profile"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-800 transition-colors"
                                @click="dropdownOpen = false"
                            >
                                <i class="pi pi-user text-sm" />
                                Profil Saya
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors text-left"
                                @click="dropdownOpen = false"
                            >
                                <i class="pi pi-sign-out text-sm" />
                                Keluar
                            </Link>
                        </div>
                    </Transition>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>

        <Toast position="top-right" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const dropdownOpen = ref(false);

const page = usePage();
const toast = useToast();

const userRole = computed(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles[0]?.name ?? '';
});

const isActive = (href) => page.url.startsWith(href);

// Generate Indonesian breadcrumbs based on route segments
const breadcrumbs = computed(() => {
    const url = page.url.split('?')[0];
    const segments = url.split('/').filter(Boolean);
    
    const segmentMap = {
        owner: 'Owner',
        cashier: 'Kasir',
        staff: 'Staf',
        dashboard: 'Dashboard',
        reports: 'Laporan Keuangan',
        'menu-items': 'Menu',
        categories: 'Kategori',
        tables: 'Meja',
        profile: 'Profil Saya',
        expenses: 'Pengeluaran',
        pos: 'POS Kasir',
        orders: 'Order Aktif',
        queue: 'Antrian Order',
    };
    
    return segments.map(seg => segmentMap[seg] || seg.charAt(0).toUpperCase() + seg.slice(1));
});

// Watch for session flash messages
watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        toast.add({ severity: 'success', summary: 'Sukses', detail: newFlash.success, life: 4000 });
    }
    if (newFlash?.error) {
        toast.add({ severity: 'error', summary: 'Error', detail: newFlash.error, life: 5000 });
    }
}, { deep: true, immediate: true });

// Listen to Reverb private channels for realtime toasts
onMounted(() => {
    if (window.Echo) {
        if (userRole.value === 'cashier') {
            window.Echo.private('cashier')
                .listen('NewOrderReceived', (e) => {
                    toast.add({
                        severity: 'info',
                        summary: 'Pesanan Baru Masuk!',
                        detail: `Order ${e.order_number} dari Meja ${e.table_name} (${e.items_count} item). Total: Rp ${e.total.toLocaleString('id-ID')}`,
                        life: 6000
                    });
                });
        }

        if (userRole.value === 'owner') {
            window.Echo.private('owner')
                .listen('ExpenseSubmitted', (e) => {
                    toast.add({
                        severity: 'warn',
                        summary: 'Pengajuan Pengeluaran!',
                        detail: `${e.submitted_by_name} mengajukan Rp ${e.amount.toLocaleString('id-ID')} untuk ${e.title} (${e.category_name})`,
                        life: 7000
                    });
                });
        }
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('cashier');
        window.Echo.leave('owner');
    }
});

const ownerNav = [
    { label: 'Dashboard', href: '/owner/dashboard', icon: 'pi pi-home' },
    { label: 'Laporan Keuangan', href: '/owner/reports', icon: 'pi pi-chart-bar' },
    { label: 'Menu', href: '/owner/menu-items', icon: 'pi pi-book' },
    { label: 'Meja', href: '/owner/tables', icon: 'pi pi-qrcode' },
    { label: 'Staff', href: '/owner/staff', icon: 'pi pi-users' },
    { label: 'Pengeluaran', href: '/owner/expenses', icon: 'pi pi-wallet' },
];

const cashierNav = [
    { label: 'Dashboard', href: '/cashier/dashboard', icon: 'pi pi-home' },
    { label: 'POS', href: '/cashier/pos', icon: 'pi pi-shopping-cart' },
    { label: 'Order Aktif', href: '/cashier/orders', icon: 'pi pi-list' },
    { label: 'Pengeluaran', href: '/cashier/expenses', icon: 'pi pi-wallet' },
];

const staffNav = [
    { label: 'Antrian Order', href: '/staff/queue', icon: 'pi pi-list' },
];

const navItems = computed(() => {
    if (userRole.value === 'owner') return ownerNav;
    if (userRole.value === 'cashier') return cashierNav;
    return staffNav;
});
</script>
