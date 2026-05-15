<template>
    <AuthLayout title="Register">
        <AuthCard>
            <SocialButtons @google="registerWithGoogle" @facebook="registerWithFacebook" />

            <div class="position-relative text-center my-4">
                <p class="mb-0 fs-4 px-3 d-inline-block bg-body text-dark z-index-5 position-relative">
                    or register with
                </p>
                <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
            </div>

            <form @submit.prevent="submit">
                <AppInput
                    v-model="form.name"
                    label="Full Name"
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
                    v-model="form.password"
                    type="password"
                    label="Password"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    class="mb-3"
                />

                <AppInput
                    v-model="form.password_confirmation"
                    type="password"
                    label="Confirm Password"
                    :error="form.errors.password_confirmation"
                    autocomplete="new-password"
                    class="mb-4"
                />

                <button
                    type="submit"
                    class="btn btn-primary w-100 py-8 mb-4 rounded-2"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Create Account
                </button>

                <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-medium">Already have an account?</p>
                    <Link href="/login" class="text-primary fw-medium ms-2">Sign In</Link>
                </div>
            </form>
        </AuthCard>
    </AuthLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import SocialButtons from '@/Components/Auth/SocialButtons.vue';
import { AppInput } from '@/Components/UI/Form';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}

function registerWithGoogle() {
    // TODO: implement Google OAuth
}

function registerWithFacebook() {
    // TODO: implement Facebook OAuth
}
</script>
