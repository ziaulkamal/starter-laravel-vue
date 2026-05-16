<template>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h5 class="fw-semibold mb-1">Ubah Email</h5>
            <p class="text-muted small mb-4">
                Konfirmasi dengan password saat ini untuk mengganti email.
            </p>

            <form @submit.prevent="submit">
                <AppInput
                    v-model="form.email"
                    type="email"
                    label="Email Baru"
                    placeholder="email@contoh.com"
                    :error="form.errors.email"
                    autocomplete="email"
                    class="mb-3"
                />

                <!-- Password konfirmasi dengan toggle -->
                <div class="mb-4">
                    <label class="form-label">Password Saat Ini</label>
                    <div class="input-group">
                        <input
                            v-model="form.current_password"
                            :type="showPass ? 'text' : 'password'"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.current_password }"
                            placeholder="Masukkan password saat ini"
                            autocomplete="current-password"
                        />
                        <button type="button"
                                class="input-group-text bg-transparent"
                                @click="showPass = !showPass"
                                tabindex="-1">
                            <i :class="showPass ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
                        </button>
                        <div v-if="form.errors.current_password" class="invalid-feedback">
                            {{ form.errors.current_password }}
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"
                            :disabled="form.processing">
                        <span v-if="form.processing"
                              class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="ti ti-mail-forward me-1"></i>
                        Simpan Email
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
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { AppInput } from '@/Components/UI/Form';

const props = defineProps<{ currentEmail: string }>();

const showPass = ref(false);

const form = useForm({
    email:            props.currentEmail,
    current_password: '',
});

function reset() {
    form.email            = props.currentEmail;
    form.current_password = '';
    showPass.value        = false;
    form.clearErrors();
}

function submit() {
    form.put('/profile/email', {
        preserveScroll: true,
        onSuccess: () => {
            form.current_password = '';
            showPass.value        = false;
        },
    });
}
</script>
