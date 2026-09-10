<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { UserRound, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { uploadImage } from '@/lib/uploadImage';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as { name: string; name_dv?: string | null; avatar?: string | null; email: string; email_verified_at?: string | null });

const avatarUrl = ref<string>(user.value.avatar ?? '');
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
        avatarUrl.value = await uploadImage(file);
    } catch {
        window.alert('Upload failed. Please try a smaller image (max 5 MB).');
    } finally {
        avatarUploading.value = false;
    }
}
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Update your name, profile picture and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="avatar" :value="avatarUrl" />

            <div class="flex items-center gap-4">
                <div class="size-16 shrink-0 overflow-hidden rounded-full border border-border bg-muted">
                    <img v-if="avatarUrl" :src="avatarUrl" alt="" class="size-full object-cover" />
                    <div v-else class="grid size-full place-items-center text-muted-foreground">
                        <UserRound class="size-7" />
                    </div>
                </div>
                <div>
                    <button type="button" :disabled="avatarUploading" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-foreground/80 transition hover:bg-muted disabled:opacity-60" @click="avatarInput?.click()">
                        <Upload class="size-3.5" :class="avatarUploading ? 'animate-pulse' : ''" /> {{ avatarUploading ? 'Uploading…' : 'Upload profile picture' }}
                    </button>
                    <button v-if="avatarUrl" type="button" class="ms-2 text-xs font-medium text-muted-foreground hover:text-red-600" @click="avatarUrl = ''">Remove</button>
                    <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="uploadAvatar" />
                    <InputError class="mt-1" :message="errors.avatar" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="name">Full name (English)</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="name_dv">Full name (Dhivehi)</Label>
                <Input
                    id="name_dv"
                    class="mt-1 block w-full font-thaana"
                    name="name_dv"
                    dir="rtl"
                    :default-value="user.name_dv ?? ''"
                    placeholder="ދިވެހި ނަން"
                />
                <InputError class="mt-2" :message="errors.name_dv" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
