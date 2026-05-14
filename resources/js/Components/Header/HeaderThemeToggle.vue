<template>
    <li class="nav-item nav-icon-hover-bg rounded-circle">
        <a class="nav-link" href="javascript:void(0)" @click.prevent="toggleTheme">
            <i :class="isDark ? 'ti ti-sun sun' : 'ti ti-moon moon'"></i>
        </a>
    </li>
</template>

<script setup>
import { useTheme } from '@/Composables/useTheme';

const { isDark, toggleTheme } = useTheme();
</script>
