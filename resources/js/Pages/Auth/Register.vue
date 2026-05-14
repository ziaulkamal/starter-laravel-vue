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
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.name }"
                        autocomplete="name"
                        autofocus
                    />
                    <div v-if="form.errors.name" class="invalid-feedback">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.email }"
                        autocomplete="email"
                    />
                    <div v-if="form.errors.email" class="invalid-feedback">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password }"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password" class="invalid-feedback">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password_confirmation }"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

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
