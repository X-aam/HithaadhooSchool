<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload, UserRound } from '@lucide/vue';
import { computed, ref } from 'vue';
import { uploadImage } from '@/lib/uploadImage';

interface UserData {
    id: number;
    name: string;
    name_dv: string | null;
    avatar: string | null;
    email: string;
    role: string;
}

interface RoleOption {
    value: string;
    label: string;
    description: string;
}

const props = defineProps<{ user: UserData | null; roles: RoleOption[] }>();

const isEdit = computed(() => props.user !== null);

const form = useForm({
    name: props.user?.name ?? '',
    name_dv: props.user?.name_dv ?? '',
    avatar: props.user?.avatar ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'editor',
    password: '',
    password_confirmation: '',
});

const inputClass =
    'w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30';

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarUploading = ref(false);

async function uploadAvatar(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
return;
}

    avatarUploading.value = true;

    try {
        form.avatar = await uploadImage(file);
    } catch {
        window.alert('Upload failed. Please try a smaller image (max 5 MB).');
    } finally {
        avatarUploading.value = false;
    }
}

function submit() {
    if (isEdit.value) {
        form.put(`/admin/users/${props.user!.id}`, {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    } else {
        form.post('/admin/users', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit user — CMS' : 'New user — CMS'" />

    <Link href="/admin/users" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand">
        <ArrowLeft class="size-4" /> Back to users
    </Link>

    <h1 class="mt-4 mb-6 text-2xl font-bold tracking-tight">{{ isEdit ? 'Edit user' : 'New user' }}</h1>

    <form class="max-w-xl space-y-6" @submit.prevent="submit">
        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <div class="space-y-5">
                <!-- Profile picture -->
                <div class="flex items-center gap-4">
                    <div class="size-16 shrink-0 overflow-hidden rounded-full border border-border bg-muted">
                        <img v-if="form.avatar" :src="form.avatar" alt="" class="size-full object-cover" />
                        <div v-else class="grid size-full place-items-center text-muted-foreground">
                            <UserRound class="size-7" />
                        </div>
                    </div>
                    <div>
                        <button type="button" :disabled="avatarUploading" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-foreground/80 transition hover:bg-muted disabled:opacity-60" @click="avatarInput?.click()">
                            <Upload class="size-3.5" :class="avatarUploading ? 'animate-pulse' : ''" /> {{ avatarUploading ? 'Uploading…' : 'Upload profile picture' }}
                        </button>
                        <button v-if="form.avatar" type="button" class="ms-2 text-xs font-medium text-muted-foreground hover:text-red-600" @click="form.avatar = ''">Remove</button>
                        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="uploadAvatar" />
                        <p v-if="form.errors.avatar" class="mt-1 text-xs text-red-600">{{ form.errors.avatar }}</p>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Full name (English)</label>
                        <input v-model="form.name" type="text" :class="inputClass" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Full name (Dhivehi)</label>
                        <input v-model="form.name_dv" type="text" dir="rtl" :class="[inputClass, 'font-thaana']" />
                        <p v-if="form.errors.name_dv" class="mt-1 text-xs text-red-600">{{ form.errors.name_dv }}</p>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Email</label>
                    <input v-model="form.email" type="email" dir="ltr" :class="inputClass" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Role</label>
                    <select v-model="form.role" :class="inputClass">
                        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">{{ roles.find((r) => r.value === form.role)?.description }}</p>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">
                        Password
                        <span v-if="isEdit" class="font-normal text-muted-foreground">(leave blank to keep current)</span>
                    </label>
                    <input v-model="form.password" type="password" autocomplete="new-password" :class="inputClass" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Confirm password</label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password" :class="inputClass" />
                </div>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60">
                <Save class="size-4" /> {{ isEdit ? 'Save changes' : 'Create user' }}
            </button>
            <Link href="/admin/users" class="text-sm font-medium text-muted-foreground hover:text-foreground">Cancel</Link>
        </div>
    </form>
</template>
