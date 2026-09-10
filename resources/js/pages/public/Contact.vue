<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { AtSign, Clock, Globe, Mail, MapPin, Phone, Send, Share2 } from '@/lib/publicIcons';
import { school as defaultSchool } from '@/lib/sampleData';

const props = defineProps<{ school?: typeof defaultSchool }>();

const { t, pick, messages } = useLocale();

const school = computed(() => props.school ?? defaultSchool);

const form = reactive({ name: '', email: '', subject: '', message: '' });
const errors = reactive<Record<string, string>>({});
const sending = ref(false);
const sent = ref(false);

const mapSrc = computed(() => {
    const { mapLat, mapLng } = school.value.contact;
    const d = 0.01;
    const bbox = `${mapLng - d},${mapLat - d},${mapLng + d},${mapLat + d}`;

    return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${mapLat},${mapLng}`;
});

const social = computed(() => [
    { key: 'facebook', href: school.value.contact.social.facebook, icon: Globe, label: 'Facebook' },
    { key: 'instagram', href: school.value.contact.social.instagram, icon: AtSign, label: 'Instagram' },
    { key: 'youtube', href: school.value.contact.social.youtube, icon: Share2, label: 'YouTube' },
    { key: 'x', href: school.value.contact.social.x, icon: Globe, label: 'X' },
]);

function validate() {
    errors.name = form.name.trim() ? '' : t(messages.contact.required);
    errors.email = !form.email.trim()
        ? t(messages.contact.required)
        : /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
          ? ''
          : t(messages.contact.invalidEmail);
    errors.subject = form.subject.trim() ? '' : t(messages.contact.required);
    errors.message = form.message.trim() ? '' : t(messages.contact.required);

    return !errors.name && !errors.email && !errors.subject && !errors.message;
}

function submit() {
    if (!validate()) {
return;
}

    sending.value = true;
    // Simulated submit — wire to a backend endpoint later.
    setTimeout(() => {
        sending.value = false;
        sent.value = true;
        form.name = form.email = form.subject = form.message = '';
    }, 900);
}
</script>

<template>
    <Head :title="t(messages.nav.contact)" />


    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <!-- Form -->
            <Reveal>
                <form class="rounded-3xl border border-border bg-background p-6 shadow-sm sm:p-8" @submit.prevent="submit" novalidate>
                    <h2 class="text-xl font-bold text-foreground">{{ t(messages.contact.title) }}</h2>

                    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-1">
                        <p v-if="sent" class="mt-4 rounded-xl bg-brand-muted px-4 py-3 text-sm font-medium text-brand" dir="auto">{{ t(messages.contact.sent) }}</p>
                    </Transition>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground" for="c-name">{{ t(messages.contact.name) }}</label>
                            <input id="c-name" v-model="form.name" type="text" dir="auto" class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20" />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground" for="c-email">{{ t(messages.contact.email) }}</label>
                            <input id="c-email" v-model="form.email" type="email" dir="ltr" class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20" />
                            <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground" for="c-subject">{{ t(messages.contact.subject) }}</label>
                            <input id="c-subject" v-model="form.subject" type="text" dir="auto" class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20" />
                            <p v-if="errors.subject" class="mt-1 text-xs text-red-600">{{ errors.subject }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground" for="c-message">{{ t(messages.contact.message) }}</label>
                            <textarea id="c-message" v-model="form.message" rows="5" dir="auto" class="w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
                            <p v-if="errors.message" class="mt-1 text-xs text-red-600">{{ errors.message }}</p>
                        </div>
                    </div>

                    <button type="submit" :disabled="sending" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold text-brand-foreground transition hover:brightness-110 disabled:opacity-70">
                        <Send class="size-4" :class="sending ? 'animate-pulse' : ''" />
                        {{ sending ? t(messages.contact.sending) : t(messages.contact.send) }}
                    </button>
                </form>
            </Reveal>

            <!-- Info + map -->
            <Reveal :delay="120" class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-border bg-background p-5 shadow-sm">
                        <MapPin class="size-6 text-brand" />
                        <h3 class="mt-3 text-sm font-semibold text-foreground">{{ t(messages.contact.address) }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground" dir="auto">{{ pick(school.contact.address) }}</p>
                    </div>
                    <div class="rounded-2xl border border-border bg-background p-5 shadow-sm">
                        <Phone class="size-6 text-brand" />
                        <h3 class="mt-3 text-sm font-semibold text-foreground">{{ t(messages.contact.phone) }}</h3>
                        <a :href="`tel:${school.contact.phone}`" dir="ltr" class="mt-1 block text-sm text-muted-foreground hover:text-brand">{{ school.contact.phone }}</a>
                        <a :href="`mailto:${school.contact.email}`" dir="ltr" class="mt-2 inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-brand"><Mail class="size-4" />{{ school.contact.email }}</a>
                    </div>
                    <div class="rounded-2xl border border-border bg-background p-5 shadow-sm sm:col-span-2">
                        <Clock class="size-6 text-brand" />
                        <h3 class="mt-3 text-sm font-semibold text-foreground">{{ t(messages.contact.officeHours) }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground" dir="auto">{{ pick(school.contact.officeHours) }}</p>
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">{{ t(messages.contact.followUs) }}</h4>
                            <div class="mt-2 flex gap-2">
                                <a v-for="s in social" :key="s.key" :href="s.href" target="_blank" rel="noopener noreferrer" :aria-label="s.label" class="grid size-9 place-items-center rounded-full bg-brand-muted text-brand transition hover:bg-brand hover:text-brand-foreground">
                                    <component :is="s.icon" class="size-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-border shadow-sm">
                    <iframe :src="mapSrc" class="h-64 w-full" style="border: 0" loading="lazy" :title="pick(school.contact.address)"></iframe>
                </div>
            </Reveal>
        </div>
    </div>
</template>
