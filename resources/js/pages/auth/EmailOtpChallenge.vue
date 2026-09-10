<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { resend } from '@/routes/email-otp';
import { store } from '@/routes/email-otp/login';

defineOptions({
    layout: {
        title: 'Check your email',
        description: 'We sent a 6-digit verification code to your email address. Enter it below to finish signing in.',
    },
});

defineProps<{
    maskedEmail: string;
    status?: string;
}>();

const code = ref<string>('');
const resending = ref<boolean>(false);

function resendCode() {
    router.post(
        resend.url(),
        {},
        {
            preserveScroll: true,
            onStart: () => (resending.value = true),
            onFinish: () => (resending.value = false),
        },
    );
}
</script>

<template>
    <Head title="Email verification code" />

    <div class="space-y-6">
        <div
            v-if="status"
            class="text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <p class="text-center text-sm text-muted-foreground">
            Code sent to <span class="font-medium text-foreground">{{ maskedEmail }}</span>
        </p>

        <Form
            v-bind="store.form()"
            class="space-y-4"
            reset-on-error
            @error="code = ''"
            #default="{ errors, processing }"
        >
            <input type="hidden" name="code" :value="code" />
            <div
                class="flex flex-col items-center justify-center space-y-3 text-center"
            >
                <div class="flex w-full items-center justify-center">
                    <InputOTP
                        id="otp"
                        v-model="code"
                        :maxlength="6"
                        :disabled="processing"
                        autofocus
                    >
                        <InputOTPGroup>
                            <InputOTPSlot
                                v-for="index in 6"
                                :key="index"
                                :index="index - 1"
                            />
                        </InputOTPGroup>
                    </InputOTP>
                </div>
                <InputError :message="errors.code" />
            </div>
            <Button type="submit" class="w-full" :disabled="processing"
                >Continue</Button
            >
        </Form>

        <div class="text-center text-sm text-muted-foreground">
            <span>Didn't receive it? </span>
            <button
                type="button"
                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                :disabled="resending"
                @click="resendCode"
            >
                {{ resending ? 'Sending…' : 'Send a new code' }}
            </button>
        </div>
    </div>
</template>
