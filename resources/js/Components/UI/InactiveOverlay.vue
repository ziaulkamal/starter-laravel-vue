<template>
    <Teleport to="body">
        <div class="inactive-overlay">
            <div class="inactive-card card shadow border-0 text-center p-4 p-md-5">
                <div class="mb-4">
                    <span
                        class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                        style="width: 76px; height: 76px"
                    >
                        <i class="ti ti-lock text-danger" style="font-size: 2.2rem"></i>
                    </span>
                </div>

                <h4 class="fw-bold mb-2">Akun Anda Nonaktif</h4>

                <p class="text-muted mb-4" style="line-height: 1.7">
                    Akun Anda sedang dinonaktifkan oleh administrator.<br>
                    Hubungi administrator untuk mengaktifkan kembali akun Anda.
                </p>

                <div>
                    <button class="btn btn-danger px-5" @click="emit('logout')">
                        <i class="ti ti-logout me-2 fs-5"></i>Keluar
                    </button>
                </div>

                <p class="text-muted small mt-4 mb-0" style="opacity: .5">
                    Jika ini adalah kesalahan, hubungi tim IT Anda.
                </p>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
const emit = defineEmits<{ logout: [] }>();
</script>

<style scoped>
.inactive-overlay {
    position: fixed;
    inset: 0;
    z-index: 9990;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px) brightness(0.65);
    -webkit-backdrop-filter: blur(10px) brightness(0.65);
    background: rgba(0, 0, 0, 0.35);
}

.inactive-card {
    max-width: 440px;
    width: calc(100% - 2rem);
    animation: fadeInUp .25s ease both;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
