<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ExternalLink,
    Link2,
    Send,
    ShieldAlert,
    Unlink,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { edit } from '@/routes/mail';

interface OAuthProvider {
    value: string;
    label: string;
    console: string;
}

interface MailSettings {
    mailer: string;
    auth_type: string;
    host: string | null;
    port: number | null;
    encryption: string;
    username: string | null;
    from_address: string | null;
    from_name: string | null;
    verify_peer: boolean;
    oauth_client_id: string | null;
    oauth_email: string | null;
    oauth_tenant: string | null;
}

const props = defineProps<{
    settings: MailSettings;
    hasPassword: boolean;
    hasClientSecret: boolean;
    isConnected: boolean;
    tokenExpiresAt: string | null;
    providers: OAuthProvider[];
    redirectUri: string;
    isSaved: boolean;
    lastTestedAt: string | null;
    mailers: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Email settings', href: edit() }],
    },
});

const form = useForm({
    mailer: props.settings.mailer,
    auth_type: props.settings.auth_type ?? 'password',
    host: props.settings.host ?? '',
    port: props.settings.port ?? 587,
    encryption: props.settings.encryption ?? 'tls',
    username: props.settings.username ?? '',
    password: '',
    from_address: props.settings.from_address ?? '',
    from_name: props.settings.from_name ?? '',
    verify_peer: props.settings.verify_peer,
    oauth_client_id: props.settings.oauth_client_id ?? '',
    oauth_client_secret: '',
    oauth_email: props.settings.oauth_email ?? '',
    oauth_tenant: props.settings.oauth_tenant ?? '',
});

const isSmtp = computed(() => form.mailer === 'smtp');
const usesOAuth = computed(() => form.auth_type !== 'password');

const activeProvider = computed(() =>
    props.providers.find((p) => p.value === form.auth_type),
);

/** The provider dictates its own SMTP host, so fill it in on selection. */
const PROVIDER_SMTP: Record<string, { host: string; port: number }> = {
    microsoft: { host: 'smtp.office365.com', port: 587 },
    google: { host: 'smtp.gmail.com', port: 587 },
};

function onAuthTypeChange() {
    const smtp = PROVIDER_SMTP[form.auth_type];

    if (smtp) {
        form.mailer = 'smtp';
        form.host = smtp.host;
        form.port = smtp.port;
        form.encryption = 'tls';
    }
}

/** Consent is granted against saved settings, so unsaved edits must go first. */
function connect() {
    if (form.isDirty) {
        window.alert('Save your changes before connecting.');

        return;
    }

    window.location.href = '/settings/mail/connect';
}

function disconnect() {
    if (
        !window.confirm(
            'Disconnect this mailbox? The site will stop sending email until it is reconnected.',
        )
    ) {
        return;
    }

    router.delete('/settings/mail/connect', { preserveScroll: true });
}

async function copyRedirectUri() {
    await navigator.clipboard.writeText(props.redirectUri);
}

const testEmail = ref('');
const testing = ref(false);

const lastTested = computed(() =>
    props.lastTestedAt
        ? new Date(props.lastTestedAt).toLocaleString('en-GB', {
              dateStyle: 'medium',
              timeStyle: 'short',
          })
        : null,
);

/** Ports imply the encryption almost every time — keep the two in step. */
function onEncryptionChange() {
    if (form.encryption === 'ssl' && form.port === 587) {
        form.port = 465;
    }

    if (form.encryption === 'tls' && form.port === 465) {
        form.port = 587;
    }
}

function save() {
    form.put('/settings/mail', {
        preserveScroll: true,
        onSuccess: () => form.reset('password'),
    });
}

function sendTest() {
    if (!testEmail.value.trim()) {
        return;
    }

    testing.value = true;

    router.post(
        '/settings/mail/test',
        { email: testEmail.value },
        {
            preserveScroll: true,
            onFinish: () => {
                testing.value = false;
            },
        },
    );
}

const field =
    'w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20';
const labelClass = 'mb-1.5 block text-sm font-medium';
</script>

<template>
    <Head title="Email settings" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Email server"
            description="How the website sends password resets, login codes and notifications"
        />

        <div
            v-if="!isSaved"
            class="flex items-start gap-2.5 rounded-xl border border-border bg-muted/40 px-4 py-3 text-sm text-muted-foreground"
        >
            <ShieldAlert class="mt-0.5 size-4 shrink-0" />
            <span>
                No settings saved yet — the site is using the values from its
                <code class="rounded bg-background px-1 py-0.5 text-xs"
                    >.env</code
                >
                file. Saving here overrides them.
            </span>
        </div>

        <form class="space-y-5" @submit.prevent="save">
            <div>
                <label for="mailer" :class="labelClass">Sending method</label>
                <select id="mailer" v-model="form.mailer" :class="field">
                    <option v-for="m in props.mailers" :key="m" :value="m">
                        {{
                            m === 'smtp'
                                ? 'SMTP server'
                                : m === 'log'
                                  ? 'Write to log file (no email sent)'
                                  : m === 'array'
                                    ? 'Discard (testing only)'
                                    : 'Sendmail (server binary)'
                        }}
                    </option>
                </select>
                <InputError :message="form.errors.mailer" class="mt-1.5" />
                <p v-if="!isSmtp" class="mt-1.5 text-xs text-muted-foreground">
                    No mail will reach real inboxes with this method.
                </p>
            </div>

            <div>
                <label for="auth_type" :class="labelClass"
                    >Sign-in method</label
                >
                <select
                    id="auth_type"
                    v-model="form.auth_type"
                    :class="field"
                    @change="onAuthTypeChange"
                >
                    <option value="password">Username and password</option>
                    <option
                        v-for="p in props.providers"
                        :key="p.value"
                        :value="p.value"
                    >
                        {{ p.label }} (modern authentication)
                    </option>
                </select>
                <InputError :message="form.errors.auth_type" class="mt-1.5" />
                <p class="mt-1.5 text-xs text-muted-foreground">
                    Microsoft 365 and Google are withdrawing password sign-in
                    for SMTP. Modern authentication (OAuth 2) is the supported
                    way to send through them.
                </p>
            </div>

            <!-- OAuth application details -->
            <template v-if="usesOAuth">
                <div
                    class="space-y-4 rounded-xl border border-border bg-muted/30 p-4"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-semibold">
                            {{ activeProvider?.label }} application
                        </h3>
                        <a
                            v-if="activeProvider"
                            :href="activeProvider.console"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1 text-xs font-medium text-brand hover:underline"
                        >
                            Register an app <ExternalLink class="size-3" />
                        </a>
                    </div>

                    <div>
                        <label :class="labelClass">Redirect URI</label>
                        <div class="flex items-center gap-2">
                            <code
                                class="min-w-0 flex-1 truncate rounded-lg bg-background px-3 py-2 font-mono text-xs"
                            >
                                {{ props.redirectUri }}
                            </code>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg border border-border px-2.5 py-2 text-xs font-medium transition hover:bg-muted"
                                @click="copyRedirectUri"
                            >
                                Copy
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-muted-foreground">
                            Add this to the app registration exactly as shown,
                            or the provider will refuse the sign-in.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="oauth_client_id" :class="labelClass">
                                Application (client) ID
                            </label>
                            <input
                                id="oauth_client_id"
                                v-model="form.oauth_client_id"
                                type="text"
                                autocomplete="off"
                                :class="field"
                            />
                            <InputError
                                :message="form.errors.oauth_client_id"
                                class="mt-1.5"
                            />
                        </div>
                        <div>
                            <label
                                for="oauth_client_secret"
                                :class="labelClass"
                            >
                                Client secret
                            </label>
                            <input
                                id="oauth_client_secret"
                                v-model="form.oauth_client_secret"
                                type="password"
                                autocomplete="new-password"
                                :placeholder="
                                    hasClientSecret
                                        ? '•••••••• (unchanged)'
                                        : 'Value from the app registration'
                                "
                                :class="field"
                            />
                            <InputError
                                :message="form.errors.oauth_client_secret"
                                class="mt-1.5"
                            />
                            <p class="mt-1.5 text-xs text-muted-foreground">
                                {{
                                    hasClientSecret
                                        ? 'Leave blank to keep the saved secret.'
                                        : 'Stored encrypted. Never shown again after saving.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="oauth_email" :class="labelClass">
                                Mailbox address
                            </label>
                            <input
                                id="oauth_email"
                                v-model="form.oauth_email"
                                type="email"
                                placeholder="noreply@school.edu.mv"
                                :class="field"
                            />
                            <InputError
                                :message="form.errors.oauth_email"
                                class="mt-1.5"
                            />
                            <p class="mt-1.5 text-xs text-muted-foreground">
                                The account that will send the mail, and the one
                                you sign in as when connecting.
                            </p>
                        </div>
                        <div v-if="form.auth_type === 'microsoft'">
                            <label for="oauth_tenant" :class="labelClass">
                                Directory (tenant) ID
                                <span class="font-normal text-muted-foreground">
                                    — optional
                                </span>
                            </label>
                            <input
                                id="oauth_tenant"
                                v-model="form.oauth_tenant"
                                type="text"
                                placeholder="common"
                                :class="field"
                            />
                            <InputError
                                :message="form.errors.oauth_tenant"
                                class="mt-1.5"
                            />
                            <p class="mt-1.5 text-xs text-muted-foreground">
                                Leave blank unless the app is restricted to a
                                single directory.
                            </p>
                        </div>
                    </div>

                    <!-- Connection state -->
                    <div
                        class="flex flex-wrap items-center gap-3 border-t border-border pt-4"
                    >
                        <template v-if="isConnected">
                            <span
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400"
                            >
                                <CheckCircle2 class="size-4" />
                                Connected as {{ props.settings.oauth_email }}
                            </span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-full border border-border px-3 py-1.5 text-xs font-medium transition hover:bg-muted"
                                @click="connect"
                            >
                                <Link2 class="size-3.5" /> Reconnect
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-destructive transition hover:bg-destructive/10"
                                @click="disconnect"
                            >
                                <Unlink class="size-3.5" /> Disconnect
                            </button>
                        </template>
                        <template v-else>
                            <span
                                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground"
                            >
                                <ShieldAlert class="size-4" />
                                Not connected — no mail can be sent yet.
                            </span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                                @click="connect"
                            >
                                <Link2 class="size-4" />
                                Connect {{ activeProvider?.label }}
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <template v-if="isSmtp">
                <div class="grid gap-4 sm:grid-cols-[1fr_auto_auto]">
                    <div>
                        <label for="host" :class="labelClass"
                            >SMTP server</label
                        >
                        <input
                            id="host"
                            v-model="form.host"
                            type="text"
                            placeholder="smtp.office365.com"
                            :class="field"
                        />
                        <InputError
                            :message="form.errors.host"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="port" :class="labelClass">Port</label>
                        <input
                            id="port"
                            v-model.number="form.port"
                            type="number"
                            min="1"
                            max="65535"
                            :class="[field, 'w-24']"
                        />
                        <InputError
                            :message="form.errors.port"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="encryption" :class="labelClass"
                            >Encryption</label
                        >
                        <select
                            id="encryption"
                            v-model="form.encryption"
                            :class="[field, 'w-28']"
                            @change="onEncryptionChange"
                        >
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">None</option>
                        </select>
                        <InputError
                            :message="form.errors.encryption"
                            class="mt-1.5"
                        />
                    </div>
                </div>

                <div v-if="!usesOAuth" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="username" :class="labelClass"
                            >Username</label
                        >
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            autocomplete="off"
                            placeholder="mail@school.edu.mv"
                            :class="field"
                        />
                        <InputError
                            :message="form.errors.username"
                            class="mt-1.5"
                        />
                    </div>
                    <div>
                        <label for="password" :class="labelClass"
                            >Password</label
                        >
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            :placeholder="
                                hasPassword
                                    ? '•••••••• (unchanged)'
                                    : 'App password'
                            "
                            :class="field"
                        />
                        <InputError
                            :message="form.errors.password"
                            class="mt-1.5"
                        />
                        <p class="mt-1.5 text-xs text-muted-foreground">
                            {{
                                hasPassword
                                    ? 'Leave blank to keep the saved password.'
                                    : 'Stored encrypted. Never shown again after saving.'
                            }}
                        </p>
                    </div>
                </div>

                <label class="flex items-start gap-2.5 text-sm">
                    <input
                        v-model="form.verify_peer"
                        type="checkbox"
                        class="mt-0.5 size-4 rounded border-border"
                    />
                    <span>
                        Verify the server's TLS certificate
                        <span class="block text-xs text-muted-foreground">
                            Leave on. Only turn it off on a machine where
                            antivirus intercepts TLS connections.
                        </span>
                    </span>
                </label>
            </template>

            <div class="grid gap-4 border-t border-border pt-5 sm:grid-cols-2">
                <div>
                    <label for="from_address" :class="labelClass"
                        >Send from address</label
                    >
                    <input
                        id="from_address"
                        v-model="form.from_address"
                        type="email"
                        placeholder="noreply@school.edu.mv"
                        :class="field"
                    />
                    <InputError
                        :message="form.errors.from_address"
                        class="mt-1.5"
                    />
                </div>
                <div>
                    <label for="from_name" :class="labelClass"
                        >Send from name</label
                    >
                    <input
                        id="from_name"
                        v-model="form.from_name"
                        type="text"
                        placeholder="Hithaadhoo School"
                        :class="field"
                    />
                    <InputError
                        :message="form.errors.from_name"
                        class="mt-1.5"
                    />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground transition hover:brightness-110 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving…' : 'Save settings' }}
                </button>
                <span
                    v-if="form.recentlySuccessful"
                    class="text-sm text-muted-foreground"
                >
                    Saved.
                </span>
            </div>
        </form>

        <!-- Test send -->
        <div class="rounded-xl border border-border bg-muted/30 p-4">
            <h3 class="text-sm font-semibold">Send a test email</h3>
            <p class="mt-1 mb-3 text-xs text-muted-foreground">
                Uses the saved settings, not the values typed above — save
                first.
            </p>

            <div class="flex flex-wrap items-center gap-2">
                <input
                    v-model="testEmail"
                    type="email"
                    placeholder="you@example.com"
                    :class="[field, 'w-64']"
                    @keydown.enter.prevent="sendTest"
                />
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-sm font-semibold transition hover:border-brand/40 hover:text-brand disabled:opacity-60"
                    :disabled="testing || !testEmail.trim()"
                    @click="sendTest"
                >
                    <Send class="size-4" />
                    {{ testing ? 'Sending…' : 'Send test' }}
                </button>
            </div>

            <p
                v-if="lastTested"
                class="mt-3 inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <CheckCircle2 class="size-3.5 text-emerald-600" />
                Last successful test: {{ lastTested }}
            </p>
        </div>
    </div>
</template>
