<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar -->
        <aside
            :class="['fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">NN</span>
                </div>
                <span class="font-bold text-slate-800 text-lg">Niti Noto</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <template v-for="item in navItems" :key="item.label">
                    <Link
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-amber-50 text-amber-700'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                        ]"
                    >
                        <i :class="['text-base', item.icon]" />
                        {{ item.label }}
                    </Link>
                </template>
            </nav>

            <!-- User info -->
            <div class="px-4 py-4 border-t border-slate-100">
                <div class="flex items-center gap-3 px-3 py-2">
                    <img
                        v-if="$page.props.auth.user.photo"
                        :src="`/storage/${$page.props.auth.user.photo}`"
                        class="w-8 h-8 rounded-full object-cover"
                        alt="avatar"
                    />
                    <div v-else class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                        <span class="text-amber-700 font-semibold text-xs">
                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-slate-500 capitalize">{{ userRole }}</p>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="mt-2 w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors"
                >
                    <i class="pi pi-sign-out text-base" />
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/30 z-40 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main content -->
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-4">
                <button
                    class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <i class="pi pi-bars text-lg" />
                </button>
                <h1 class="text-slate-800 font-semibold text-base">{{ title }}</h1>
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
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const sidebarOpen = ref(false);
const page = usePage();

const userRole = computed(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles[0]?.name ?? '';
});

const isActive = (href) => page.url.startsWith(href);

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
