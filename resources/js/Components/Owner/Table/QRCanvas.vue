<script setup>
import { onMounted, ref, watch } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
    url:  { type: String, required: true },
    size: { type: Number, default: 200 },
});

const canvas = ref(null);

async function render() {
    if (!canvas.value) return;
    await QRCode.toCanvas(canvas.value, props.url, {
        width: props.size,
        margin: 1,
        color: { dark: '#1e293b', light: '#ffffff' },
    });
}

onMounted(render);
watch(() => props.url, render);
</script>

<template>
    <canvas ref="canvas" :width="size" :height="size" class="block" />
</template>
