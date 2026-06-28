import { ref, computed, watch } from 'vue';

export function useCart(qrCode) {
    const key = `niti_noto_cart_${qrCode}`;

    const stored = sessionStorage.getItem(key);
    const items = ref(stored ? JSON.parse(stored) : []);

    watch(items, (val) => {
        sessionStorage.setItem(key, JSON.stringify(val));
    }, { deep: true });

    const totalQty = computed(() => items.value.reduce((s, i) => s + i.qty, 0));
    const totalPrice = computed(() => items.value.reduce((s, i) => s + i.price * i.qty, 0));

    function add(menuItem) {
        const existing = items.value.find(i => i.id === menuItem.id);
        if (existing) {
            existing.qty++;
        } else {
            items.value.push({
                id:    menuItem.id,
                name:  menuItem.name,
                price: Number(menuItem.price),
                image: menuItem.image,
                qty:   1,
            });
        }
    }

    function setQty(id, qty) {
        if (qty <= 0) {
            items.value = items.value.filter(i => i.id !== id);
        } else {
            const item = items.value.find(i => i.id === id);
            if (item) item.qty = qty;
        }
    }

    function remove(id) {
        items.value = items.value.filter(i => i.id !== id);
    }

    function clear() {
        items.value = [];
        sessionStorage.removeItem(key);
    }

    function getItemQty(id) {
        return items.value.find(i => i.id === id)?.qty ?? 0;
    }

    return { items, totalQty, totalPrice, add, setQty, remove, clear, getItemQty };
}
