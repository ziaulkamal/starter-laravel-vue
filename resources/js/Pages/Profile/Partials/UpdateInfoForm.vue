<template>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h5 class="fw-semibold mb-1">Informasi Pribadi</h5>
            <p class="text-muted small mb-4">Perbarui nama dan nomor HP Anda.</p>

            <form @submit.prevent="submit">
                <AppInput
                    v-model="form.name"
                    label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap"
                    :error="form.errors.name"
                    autocomplete="name"
                    class="mb-3"
                />

                <div class="mb-4">
                    <AppInput
                        v-model="phoneDisplay"
                        label="Nomor HP"
                        prepend-text="+62"
                        placeholder="8xxxxxxxxxx"
                        inputmode="numeric"
                        :error="form.errors.phone"
                        @keydown="blockNonNumeric"
                        @blur="handlePhoneBlur"
                    />
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"
                            :disabled="form.processing">
                        <span v-if="form.processing"
                              class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="ti ti-device-floppy me-1"></i>
                        Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-light px-4" @click="reset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast: format HP -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div id="profile-phone-toast"
             class="toast align-items-center text-bg-info border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ti-info-circle me-2"></i>
                    Nomor HP telah disesuaikan ke format Indonesia (+62).
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// @ts-expect-error
import { Toast } from 'bootstrap';
import { AppInput } from '@/Components/UI/Form';

const props = defineProps<{
    profileData: { name: string; email: string; phone: string };
}>();

const phoneDisplay = ref(props.profileData.phone);

// Strip non-numeric dari paste/autocomplete
watch(phoneDisplay, (val) => {
    const clean = val.replace(/\D/g, '');
    if (clean !== val) phoneDisplay.value = clean;
});

// Blokir non-angka dari keyboard
function blockNonNumeric(e: KeyboardEvent) {
    const allowed = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                     'ArrowLeft', 'ArrowRight', 'Home', 'End'];
    if (allowed.includes(e.key)) return;
    if ((e.ctrlKey || e.metaKey) && ['a','c','v','x','z'].includes(e.key.toLowerCase())) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
}

function handlePhoneBlur() {
    let val = phoneDisplay.value.replace(/\D/g, '');
    if (val.startsWith('0')) {
        val = val.substring(1);
        phoneDisplay.value = val;
        const el = document.getElementById('profile-phone-toast');
        if (el) new Toast(el, { delay: 3500 }).show();
    }
}

const form = useForm({
    name:  props.profileData.name,
    phone: '',
});

function reset() {
    form.name  = props.profileData.name;
    phoneDisplay.value = props.profileData.phone;
    form.clearErrors();
}

function submit() {
    const val = phoneDisplay.value.replace(/\D/g, '');
    form.phone = val
        ? (val.startsWith('0') ? '62' + val.substring(1) : '62' + val)
        : '';

    form.put('/profile/info', { preserveScroll: true });
}
</script>
