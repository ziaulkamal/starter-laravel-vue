<template>
    <AppLayout
        title="Manajemen Role & Permission"
        :breadcrumb="[{ label: 'Pengaturan' }, { label: 'Role & Permission' }]"
    >
        <!-- Flash -->
        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ $page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="row g-4">
            <div v-for="role in roles" :key="role.id" class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-semibold text-capitalize">{{ role.name }}</h6>
                            <small class="text-muted">{{ role.users_count }} user</small>
                        </div>
                        <button
                            class="btn btn-sm btn-primary"
                            :disabled="savingId === role.id"
                            @click="save(role)"
                        >
                            <span v-if="savingId === role.id" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            <i v-else class="ti ti-device-floppy me-1"></i>
                            Simpan
                        </button>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small mb-2">Centang permission yang dimiliki role ini:</p>

                        <div
                            v-for="group in groupedPermissions"
                            :key="group.module"
                            class="mb-3"
                        >
                            <div class="text-uppercase text-muted fw-semibold small mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                {{ group.module }}
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <div
                                    v-for="perm in group.permissions"
                                    :key="perm.id"
                                    class="form-check form-check-inline m-0"
                                >
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="`role-${role.id}-perm-${perm.id}`"
                                        :value="perm.id"
                                        v-model="selections[role.id]"
                                    />
                                    <label
                                        class="form-check-label small"
                                        :for="`role-${role.id}-perm-${perm.id}`"
                                    >{{ perm.action }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Permission {
    id: number;
    name: string;
}

interface RoleRow {
    id: number;
    name: string;
    users_count: number;
    permission_ids: number[];
}

const props = defineProps<{
    roles: RoleRow[];
    permissions: Permission[];
}>();

// Group permissions by module (text before the first dot)
const groupedPermissions = computed(() => {
    const map = new Map<string, { id: number; action: string }[]>();
    for (const perm of props.permissions) {
        const [module, ...rest] = perm.name.split('.');
        if (!map.has(module)) map.set(module, []);
        map.get(module)!.push({ id: perm.id, action: rest.join('.') || perm.name });
    }
    return [...map.entries()].map(([module, permissions]) => ({ module, permissions }));
});

// Reactive selections: { [roleId]: permissionId[] }
const selections = reactive<Record<number, number[]>>(
    Object.fromEntries(props.roles.map(r => [r.id, [...r.permission_ids]]))
);

const savingId = ref<number | null>(null);

function save(role: RoleRow) {
    savingId.value = role.id;
    router.put(
        `/settings/roles/${role.id}`,
        { permission_ids: selections[role.id] },
        {
            preserveScroll: true,
            onFinish: () => { savingId.value = null; },
        }
    );
}
</script>
