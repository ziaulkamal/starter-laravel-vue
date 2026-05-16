<template>
    <AuthLayout title="Login">
        <AuthCard>
            <SocialButtons @google="loginWithGoogle" @facebook="loginWithFacebook" />

            <div class="position-relative text-center my-4">
                <p class="mb-0 fs-4 px-3 d-inline-block bg-body text-dark z-index-5 position-relative">
                    or sign in with
                </p>
                <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
            </div>

            <form @submit.prevent="submit">
                <AppInput
                    v-model="form.email"
                    type="email"
                    label="Email"
                    :error="form.errors.email"
                    autocomplete="email"
                    autofocus
                    class="mb-3"
                />

                <AppInput
                    v-model="form.password"
                    type="password"
                    label="Password"
                    :error="form.errors.password"
                    autocomplete="current-password"
                    class="mb-4"
                />

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <AppCheckbox
                        v-model="form.remember"
                        label="Remember this Device"
                    />
                    <Link href="/forgot-password" class="text-primary fw-medium">
                        Forgot Password?
                    </Link>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100 py-8 mb-4 rounded-2"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Sign In
                </button>

                <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-medium">New here?</p>
                    <Link href="/register" class="text-primary fw-medium ms-2">Create an account</Link>
                </div>
            </form>
        </AuthCard>
    </AuthLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import SocialButtons from '@/Components/Auth/SocialButtons.vue';
import { AppInput, AppCheckbox } from '@/Components/UI/Form';

onMounted(() => {
    localStorage.clear();
    sessionStorage.clear();
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}

function loginWithGoogle() {
    // TODO: implement Google OAuth
}

function loginWithFacebook() {
    // TODO: implement Facebook OAuth
}
</script>
