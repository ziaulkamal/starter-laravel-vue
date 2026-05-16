<template>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h5 class="fw-semibold mb-1">Ubah Kata Sandi</h5>
            <p class="text-muted small mb-4">
                Gunakan kata sandi yang kuat dan belum pernah dipakai sebelumnya.
            </p>

            <form @submit.prevent="submit">
                <!-- Password saat ini -->
                <div class="mb-3">
                    <label class="form-label">Password Saat Ini</label>
                    <div class="input-group">
                        <input
                            v-model="form.current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.current_password }"
                            placeholder="Masukkan password saat ini"
                            autocomplete="current-password"
                        />
                        <button type="button"
                                class="input-group-text bg-transparent"
                                @click="showCurrent = !showCurrent"
                                tabindex="-1">
                            <i :class="showCurrent ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
                        </button>
                        <div v-if="form.errors.current_password" class="invalid-feedback">
                            {{ form.errors.current_password }}
                        </div>
                    </div>
                </div>

                <!-- Password baru -->
                <div class="mb-2">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input
                            v-model="form.password"
                            :type="showNew ? 'text' : 'password'"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password }"
                            placeholder="Masukkan password baru"
                            autocomplete="new-password"
                        />
                        <button type="button"
                                class="input-group-text bg-transparent"
                                @click="showNew = !showNew"
                                tabindex="-1">
                            <i :class="showNew ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
                        </button>
                        <div v-if="form.errors.password" class="invalid-feedback">
                            {{ form.errors.password }}
                        </div>
                    </div>
                </div>

                <!-- Indikator kekuatan password -->
                <div v-if="form.password.length > 0" class="d-flex flex-wrap gap-2 mb-3 ps-1">
                    <span :class="['badge','fw-normal', strength.upper  ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.upper  ? 'ti-check' : 'ti-x']"></i>Huruf Besar
                    </span>
                    <span :class="['badge','fw-normal', strength.lower  ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.lower  ? 'ti-check' : 'ti-x']"></i>Huruf Kecil
                    </span>
                    <span :class="['badge','fw-normal', strength.number ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.number ? 'ti-check' : 'ti-x']"></i>Angka
                    </span>
                    <span :class="['badge','fw-normal', strength.symbol ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.symbol ? 'ti-check' : 'ti-x']"></i>Simbol
                    </span>
                    <span :class="['badge','fw-normal', strength.length ? 'text-bg-success' : 'text-bg-secondary']">
                        <i :class="['ti me-1', strength.length ? 'ti-check' : 'ti-x']"></i>Min. 8 Karakter
                    </span>
                </div>

                <!-- Konfirmasi password -->
                <div class="mb-1">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password_confirmation }"
                            placeholder="Ulangi password baru"
                            autocomplete="new-password"
                        />
                        <button type="button"
                                class="input-group-text bg-transparent"
                                @click="showConfirm = !showConfirm"
                                tabindex="-1">
                            <i :class="showConfirm ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
                        </button>
                        <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>
                </div>

                <!-- Match indicator -->
                <div class="mb-4 ps-1" style="min-height:20px">
                    <small v-if="form.password_confirmation && passwordsMatch" class="text-success">
                        <i class="ti ti-check me-1"></i>Kata sandi cocok
                    </small>
                    <small v-else-if="form.password_confirmation && !passwordsMatch" class="text-danger">
                        <i class="ti ti-x me-1"></i>Kata sandi tidak cocok
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"
                            :disabled="form.processing">
                        <span v-if="form.processing"
                              class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="ti ti-shield-check me-1"></i>
                        Simpan Password
                    </button>
                    <button type="button" class="btn btn-light px-4" @click="reset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showCurrent = ref(false);
const showNew     = ref(false);
const showConfirm = ref(false);

const form = useForm({
    current_password:      '',
    password:              '',
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

function reset() {
    form.reset();
    showCurrent.value = false;
    showNew.value     = false;
    showConfirm.value = false;
}

function submit() {
    form.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => reset(),
    });
}
</script>
