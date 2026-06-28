<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const photoInput = ref(null);
const photoPreview = ref(null);

const form = useForm({
    _method: 'PATCH',
    name: user.name,
    email: user.email,
    photo: null,
});

function selectNewPhoto() {
    photoInput.value.click();
}

function updatePhotoPreview() {
    const file = photoInput.value.files[0];
    if (!file) return;
    form.photo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function deletePhoto() {
    form.photo = 'delete';
    photoPreview.value = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
}

function submit() {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            if (photoInput.value) {
                photoInput.value.value = '';
            }
        },
    });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Informasi Profil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Perbarui nama, alamat email, dan foto profil akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <!-- Profile Photo -->
            <div class="space-y-2">
                <InputLabel for="photo" value="Foto Profil" />
                <input
                    type="file"
                    ref="photoInput"
                    class="hidden"
                    @change="updatePhotoPreview"
                    accept="image/*"
                />

                <div class="flex items-center gap-4">
                    <!-- Current Photo / Preview -->
                    <div class="relative w-16 h-16 rounded-full overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            class="w-full h-full object-cover"
                            alt="preview"
                        />
                        <img
                            v-else-if="user.photo"
                            :src="`/storage/${user.photo}`"
                            class="w-full h-full object-cover"
                            alt="photo"
                        />
                        <div v-else class="w-full h-full bg-amber-100 flex items-center justify-center">
                            <span class="text-amber-700 font-bold text-xl">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="selectNewPhoto"
                                class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
                            >
                                Pilih Foto
                            </button>
                            <button
                                v-if="user.photo || photoPreview"
                                type="button"
                                @click="deletePhoto"
                                class="px-3 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-semibold text-red-600 shadow-sm hover:bg-red-100 transition-colors"
                            >
                                Hapus
                            </button>
                        </div>
                        <p class="text-xs text-slate-400">Format: JPG, PNG. Maks: 2MB</p>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.photo" />
            </div>

            <div>
                <InputLabel for="name" value="Nama" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Alamat email Anda belum terverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Link verifikasi baru telah dikirimkan ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
