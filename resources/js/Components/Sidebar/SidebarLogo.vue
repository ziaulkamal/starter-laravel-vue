<template>
    <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="/" class="text-nowrap logo-img">
            <img src="@images/logos/dark-logo.svg" class="dark-logo" alt="Logo-Dark" />
            <img src="@images/logos/light-logo.svg" class="light-logo" alt="Logo-Light" />
        </a>
        <a href="javascript:void(0)"
           class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none"
           @click.prevent="$emit('close-sidebar')">
            <i class="ti ti-x"></i>
        </a>
    </div>
</template>

<script setup>
defineEmits(['close-sidebar']);
</script>
