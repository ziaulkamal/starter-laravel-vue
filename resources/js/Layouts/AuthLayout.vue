<template>
    <Head :title="title ? `${title} - ${appName}` : appName" />

    <div class="preloader" v-if="loading">
        <img src="/favicon.png" alt="loader" class="lds-ripple img-fluid" />
    </div>

    <div id="main-wrapper" class="auth-customizer-none">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-4 auth-card">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
});

const appName = usePage().props.appName;

const loading = ref(true);

onMounted(() => {
    loading.value = false;
});
</script>
