<template>
    <AuthLayout title="Daftar">
        <AuthCard>
            <SocialButtons @google="registerWithGoogle" @sso="registerWithSSO" />

            <div class="position-relative text-center my-4">
                <p class="mb-0 fs-4 px-3 d-inline-block bg-body text-dark z-index-5 position-relative">
                    atau
                </p>
                <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
            </div>

            <form @submit.prevent="submit">
                <AppInput
                    v-model="form.name"
                    label="Nama Lengkap"
                    :error="form.errors.name"
                    autocomplete="name"
                    autofocus
                    class="mb-3"
                />

                <AppInput
                    v-model="form.email"
                    type="email"
                    label="Email"
                    :error="form.errors.email"
                    autocomplete="email"
                    class="mb-3"
                />

                <AppInput
                    v-model="phoneDisplay"
                    label="Nomor HP"
                    prepend-text="+62"
                    placeholder="8xxxxxxxxxx"
                    inputmode="numeric"
                    :error="form.errors.phone"
                    class="mb-3"
                    @keydown="blockNonNumeric"
                    @blur="handlePhoneBlur"
                />

                <AppInput
                    v-model="form.password"
                    type="password"
                    label="Kata Sandi"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    class="mb-2"
                />

                <div v-if="form.password.length > 0" class="d-flex flex-wrap gap-2 mb-3 ps-1">
                    <span :class="['badge', 'fw-normal', strength.upper ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.upper ? 'ti-check' : 'ti-x']"></i>Huruf Besar
                    </span>
                    <span :class="['badge', 'fw-normal', strength.lower ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.lower ? 'ti-check' : 'ti-x']"></i>Huruf Kecil
                    </span>
                    <span :class="['badge', 'fw-normal', strength.number ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.number ? 'ti-check' : 'ti-x']"></i>Angka
                    </span>
                    <span :class="['badge', 'fw-normal', strength.symbol ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.symbol ? 'ti-check' : 'ti-x']"></i>Simbol
                    </span>
                    <span :class="['badge', 'fw-normal', strength.length ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.length ? 'ti-check' : 'ti-x']"></i>Min. 8 Karakter
                    </span>
                </div>

                <AppInput
                    v-model="form.password_confirmation"
                    type="password"
                    label="Konfirmasi Kata Sandi"
                    :error="form.errors.password_confirmation"
                    autocomplete="new-password"
                    class="mb-1"
                />

                <div class="mb-4 ps-1" style="min-height: 20px">
                    <small v-if="form.password_confirmation && passwordsMatch" class="text-success">
                        <i class="ti ti-check me-1"></i>Kata sandi cocok
                    </small>
                    <small v-else-if="form.password_confirmation && !passwordsMatch" class="text-danger">
                        <i class="ti ti-x me-1"></i>Kata sandi tidak cocok
                    </small>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100 py-8 mb-4 rounded-2"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Buat Akun
                </button>

                <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-medium">Sudah punya akun?</p>
                    <Link href="/login" class="text-primary fw-medium ms-2">Masuk</Link>
                </div>
            </form>
        </AuthCard>
    </AuthLayout>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="phone-toast" class="toast align-items-center text-bg-info border-0" role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ti-info-circle me-2"></i>
                    Nomor HP telah disesuaikan ke format Indonesia (+62).
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Toast } from 'bootstrap';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import SocialButtons from '@/Components/Auth/SocialButtons.vue';
import { AppInput } from '@/Components/UI/Form';

onMounted(() => {
    localStorage.clear();
    sessionStorage.clear();
});

const phoneDisplay = ref('');

// Strip non-numeric dari paste / autocomplete
watch(phoneDisplay, (val) => {
    const clean = val.replace(/\D/g, '');
    if (clean !== val) phoneDisplay.value = clean;
});

// Blokir karakter non-angka di level keyboard
function blockNonNumeric(e) {
    const allowed = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                     'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
    if (allowed.includes(e.key)) return;
    if ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
}

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const strength = computed(() => ({
    upper:  /[A-Z]/.test(form.password),
    lower:  /[a-z]/.test(form.password),
    number: /[0-9]/.test(form.password),
    symbol: /[^a-zA-Z0-9]/.test(form.password),
    length: form.password.length >= 8,
}));

const passwordsMatch = computed(() =>
    form.password !== '' && form.password === form.password_confirmation
);

function handlePhoneBlur() {
    let val = phoneDisplay.value.replace(/\D/g, '');

    if (val.startsWith('0')) {
        val = val.substring(1);
        phoneDisplay.value = val;

        const toastEl = document.getElementById('phone-toast');
        if (toastEl) new Toast(toastEl, { delay: 3500 }).show();
    }

    form.phone = val ? '62' + val : '';
}

function submit() {
    const val = phoneDisplay.value.replace(/\D/g, '');
    form.phone = val ? (val.startsWith('0') ? '62' + val.substring(1) : '62' + val) : '';

    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}

function registerWithGoogle() {
    // TODO: implement Google OAuth
}

function registerWithSSO() {
    // TODO: implement SSO
}
</script>
