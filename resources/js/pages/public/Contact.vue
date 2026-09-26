<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { AtSign, Clock, Globe, Mail, MapPin, Phone, Share2 } from '@/lib/publicIcons';
import { school as defaultSchool } from '@/lib/sampleData';

const props = defineProps<{ school?: typeof defaultSchool }>();

const { t, pick, messages } = useLocale();

const school = computed(() => props.school ?? defaultSchool);

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
</script>

<template>
    <Head :title="t(messages.nav.contact)" />


    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <!-- Info -->
            <Reveal>
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
            </Reveal>

            <!-- Map -->
            <Reveal :delay="120" class="h-full">
                <div class="h-full overflow-hidden rounded-2xl border border-border shadow-sm">
                    <iframe :src="mapSrc" class="h-72 w-full lg:h-full" style="border: 0" loading="lazy" :title="pick(school.contact.address)"></iframe>
                </div>
            </Reveal>
        </div>
    </div>
</template>
