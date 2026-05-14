import { ref, onMounted } from 'vue';

export function useTheme() {
    const isDark = ref(false);

    function toggleTheme() {
        isDark.value = !isDark.value;
        document.documentElement.setAttribute('data-bs-theme', isDark.value ? 'dark' : 'light');
    }

    onMounted(() => {
        isDark.value = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    });

    return { isDark, toggleTheme };
}
