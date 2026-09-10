<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import LanguageSwitcher from '@/components/public/LanguageSwitcher.vue';
import SearchDialog from '@/components/public/SearchDialog.vue';
import type { LocalizedText } from '@/i18n/messages';
import { useLocale } from '@/i18n/useLocale';
import {
    CalendarClock,
    CalendarDays,
    ChevronDown,
    ClipboardList,
    Menu,
    Search,
    X,
} from '@/lib/publicIcons';

const { t, messages } = useLocale();
const page = usePage();

interface NavItem {
    label: LocalizedText;
    href?: string;
    children?: { label: LocalizedText; href: string; icon?: unknown }[];
}

interface CustomNavItem {
    id: number;
    label: LocalizedText;
    href: string;
    children?: { id: number; label: LocalizedText; href: string }[];
}

const customNav = computed(() => (page.props.siteNavigation as CustomNavItem[] | null) ?? null);

const defaultNav: NavItem[] = [
    { label: messages.nav.home, href: '/' },
    {
        label: messages.nav.calendars,
        children: [
            { label: messages.nav.academicCalendar, href: '/academic-calendar', icon: CalendarDays },
            { label: messages.nav.activityCalendar, href: '/activities', icon: CalendarClock },
            { label: messages.nav.classCalendar, href: '/timetable', icon: ClipboardList },
        ],
    },
    { label: messages.nav.news, href: '/news' },
    { label: messages.nav.announcements, href: '/announcements' },
    { label: messages.nav.downloads, href: '/downloads' },
    { label: messages.nav.orgChart, href: '/team' },
    { label: messages.nav.contact, href: '/contact' },
];

function mapCustom(item: CustomNavItem): NavItem {
    const children = (item.children ?? []).filter((c) => c.href || c.label?.en || c.label?.dv);

    if (children.length) {
        return {
            label: item.label,
            children: children.map((c) => ({ label: c.label, href: c.href })),
        };
    }

    return { label: item.label, href: item.href || '/' };
}

const nav = computed<NavItem[]>(() =>
    customNav.value && customNav.value.length
        ? customNav.value.map(mapCustom)
        : defaultNav,
);

const currentPath = computed(() => (page.url || '/').split('?')[0]);

function isActive(href?: string): boolean {
    if (!href) {
        return false;
    }

    if (href === '/') {
        return currentPath.value === '/';
    }

    return currentPath.value.startsWith(href);
}

const mobileOpen = ref(false);
const searchOpen = ref(false);
const openDropdown = ref(false);

// Close the mobile menu whenever the page changes.
watch(
    () => page.component,
    () => {
        mobileOpen.value = false;
        openDropdown.value = false;
    },
);
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-border/60 bg-background/85 backdrop-blur supports-[backdrop-filter]:bg-background/70"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2.5 rounded-md focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none">
                <img
                    src="/images/logo.png"
                    :alt="t(messages.site.name)"
                    class="size-10 shrink-0 object-contain"
                />
                <span class="text-base leading-tight font-bold text-foreground">
                    {{ t(messages.site.name) }}
                </span>
            </Link>

            <!-- Desktop nav -->
            <nav class="ms-auto hidden items-center gap-1 lg:flex" :aria-label="t(messages.common.menu)">
                <template v-for="item in nav" :key="t(item.label)">
                    <div v-if="item.children" class="group relative" @mouseenter="openDropdown = true" @mouseleave="openDropdown = false">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-foreground/80 transition hover:text-brand"
                            :aria-expanded="openDropdown"
                        >
                            {{ t(item.label) }}
                            <ChevronDown class="size-4 transition group-hover:rotate-180" />
                        </button>
                        <div
                            class="invisible absolute start-0 top-full min-w-56 translate-y-1 rounded-xl border border-border bg-popover p-1.5 opacity-0 shadow-lg transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.href"
                                :href="child.href"
                                class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-foreground/80 transition hover:bg-brand-muted hover:text-brand"
                            >
                                <component :is="child.icon" class="size-4 text-brand" />
                                {{ t(child.label) }}
                            </Link>
                        </div>
                    </div>
                    <Link
                        v-else
                        :href="item.href!"
                        class="relative rounded-md px-3 py-2 text-sm font-medium transition hover:text-brand"
                        :class="isActive(item.href) ? 'text-brand' : 'text-foreground/80'"
                    >
                        {{ t(item.label) }}
                        <span
                            class="absolute inset-x-3 -bottom-px h-0.5 origin-center scale-x-0 rounded-full bg-brand transition-transform duration-300"
                            :class="isActive(item.href) ? 'scale-x-100' : 'group-hover:scale-x-100'"
                        />
                    </Link>
                </template>
            </nav>

            <!-- Actions -->
            <div class="ms-auto flex items-center gap-2 lg:ms-2">
                <button
                    type="button"
                    class="grid size-9 place-items-center rounded-full text-foreground/70 transition hover:bg-muted hover:text-brand focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none"
                    :aria-label="t(messages.common.search)"
                    @click="searchOpen = true"
                >
                    <Search class="size-5" />
                </button>
                <LanguageSwitcher />
                <!-- Animated hamburger -->
                <button
                    type="button"
                    class="relative grid size-10 place-items-center rounded-full text-foreground transition hover:bg-muted lg:hidden"
                    :aria-label="t(messages.common.menu)"
                    :aria-expanded="mobileOpen"
                    @click="mobileOpen = !mobileOpen"
                >
                    <span class="sr-only">{{ t(messages.common.menu) }}</span>
                    <span class="relative block h-4 w-6">
                        <span class="hamburger-line absolute inset-x-0 top-0 h-0.5 rounded bg-current" :class="mobileOpen ? 'translate-y-[7px] rotate-45' : ''" />
                        <span class="hamburger-line absolute inset-x-0 top-1/2 h-0.5 -translate-y-1/2 rounded bg-current" :class="mobileOpen ? 'opacity-0' : ''" />
                        <span class="hamburger-line absolute inset-x-0 bottom-0 h-0.5 rounded bg-current" :class="mobileOpen ? '-translate-y-[7px] -rotate-45' : ''" />
                    </span>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="mobileOpen" class="border-t border-border bg-background lg:hidden">
                <nav class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6">
                    <template v-for="item in nav" :key="t(item.label)">
                        <template v-if="item.children">
                            <p class="px-3 pt-3 pb-1 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                {{ t(item.label) }}
                            </p>
                            <Link
                                v-for="child in item.children"
                                :key="child.href"
                                :href="child.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-foreground/80 transition hover:bg-brand-muted hover:text-brand"
                            >
                                <component :is="child.icon" class="size-4 text-brand" />
                                {{ t(child.label) }}
                            </Link>
                        </template>
                        <Link
                            v-else
                            :href="item.href!"
                            class="block rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-brand-muted hover:text-brand"
                            :class="isActive(item.href) ? 'bg-brand-muted text-brand' : 'text-foreground/80'"
                        >
                            {{ t(item.label) }}
                        </Link>
                    </template>
                </nav>
            </div>
        </Transition>

        <SearchDialog v-model:open="searchOpen" />
    </header>
</template>
