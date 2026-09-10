<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';

interface UserRow {
    id: number;
    name: string;
    name_dv: string | null;
    avatar: string | null;
    email: string;
    role: string;
    roleLabel: string;
    verified: boolean;
    created_at: string | null;
}

defineProps<{ users: UserRow[]; currentUserId: number | null }>();

const page = usePage();
const authId = () => (page.props.currentUserId as number | null);

function destroy(user: UserRow) {
    if (confirm(`Delete ${user.name} (${user.email})? This cannot be undone.`)) {
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Users — CMS" />

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Users</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ users.length }} user(s) with access to the CMS.</p>
        </div>
        <Link href="/admin/users/create" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110">
            <Plus class="size-4" /> New user
        </Link>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm">
        <table class="w-full text-sm">
            <thead class="border-b border-border bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Added</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <tr v-for="u in users" :key="u.id" class="hover:bg-muted/30">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="size-9 shrink-0 overflow-hidden rounded-full border border-border bg-muted">
                                <img v-if="u.avatar" :src="u.avatar" alt="" class="size-full object-cover" />
                                <div v-else class="grid size-full place-items-center text-xs font-semibold text-muted-foreground">{{ u.name.charAt(0) }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium">
                                    {{ u.name }}
                                    <span v-if="u.id === currentUserId" class="ms-1 rounded-full bg-brand-muted px-2 py-0.5 text-[11px] font-semibold text-brand">You</span>
                                </div>
                                <div v-if="u.name_dv" class="truncate text-xs text-muted-foreground" dir="rtl">{{ u.name_dv }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground" dir="ltr">{{ u.email }}</td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="u.role === 'admin' ? 'bg-brand-muted text-brand' : u.role === 'editor' ? 'bg-sky-500/15 text-sky-700 dark:text-sky-300' : 'bg-muted text-muted-foreground'"
                        >
                            {{ u.roleLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">{{ u.created_at }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <Link :href="`/admin/users/${u.id}/edit`" class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-brand" title="Edit">
                                <Pencil class="size-4" />
                            </Link>
                            <button
                                type="button"
                                class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 disabled:opacity-30 dark:hover:bg-red-900/30"
                                :disabled="u.id === currentUserId"
                                :title="u.id === currentUserId ? 'You cannot delete your own account' : 'Delete'"
                                @click="destroy(u)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!users.length">
                    <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No users yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
