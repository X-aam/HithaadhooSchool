<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    LayoutGrid,
    Newspaper,
    Megaphone,
    FileText,
    FolderOpen,
    Users,
    Settings,
    ExternalLink,
    LogOut,
    Menu,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import { dashboard, logout as logoutRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types/navigation';

/*
 * Settings and team pages declare a breadcrumb trail through Inertia's layout
 * props. They render inside this layout now, so it has to accept them.
 */
const { breadcrumbs = [] } = defineProps<{ breadcrumbs?: BreadcrumbItem[] }>();

const page = usePage();
const user = computed(
    () => page.props.auth?.user as { name: string; email: string } | undefined,
);
const currentUrl = computed(() => page.url);

// Mirrors SiteContentController::SECTIONS (keys + labels).
const contentSections = [
    { key: 'school', title: 'School information' },
    { key: 'navigation', title: 'Navigation menu' },
    { key: 'hero', title: 'Homepage hero slides' },
    { key: 'academic_events', title: 'Academic calendar' },
    { key: 'activities', title: 'Activities' },
    { key: 'timetable', title: 'Class timetable' },
    { key: 'staff', title: 'Staff & organisation' },
    { key: 'downloads', title: 'Downloads' },
];

const nav = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: LayoutDashboard,
        match: /^\/admin\/?$/,
    },
    {
        title: 'News & Blog',
        href: '/admin/news',
        icon: Newspaper,
        match: /^\/admin\/news/,
    },
    {
        title: 'Announcements',
        href: '/admin/announcements',
        icon: Megaphone,
        match: /^\/admin\/announcements/,
    },
    {
        title: 'Pages',
        href: '/admin/content',
        icon: FileText,
        match: /^\/admin\/content/,
        children: contentSections,
    },
    {
        title: 'Files',
        href: '/admin/files',
        icon: FolderOpen,
        match: /^\/admin\/files/,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
        match: /^\/admin\/users/,
    },
];

const role = computed(
    () =>
        (page.props.auth?.user as { role?: string } | undefined)?.role ??
        'author',
);
const canManageContent = computed(
    () => role.value === 'admin' || role.value === 'editor',
);
const canManageUsers = computed(() => role.value === 'admin');

const visibleNav = computed(() =>
    nav.filter((item) => {
        if (item.href === '/admin/users') {
            return canManageUsers.value;
        }

        if (
            item.href === '/admin/announcements' ||
            item.href === '/admin/content' ||
            item.href === '/admin/files'
        ) {
            return canManageContent.value;
        }

        return true;
    }),
);

const open = ref(false);

const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const platformNav = computed(() => [
    {
        title: 'App dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
        match: /\/dashboard/,
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
        match: /^\/settings/,
    },
]);

function isActive(match: RegExp) {
    return match.test(currentUrl.value);
}

function isSection(key: string) {
    return currentUrl.value.includes(`/admin/content/${key}`);
}

function logout() {
    router.post(logoutRoute().url);
}
</script>

<template>
    <div dir="ltr" class="min-h-screen bg-muted/30">
        <!-- Mobile top bar -->
        <div
            class="flex items-center justify-between border-b border-border bg-background px-4 py-3 lg:hidden"
        >
            <Link href="/admin" class="flex items-center gap-2 font-semibold">
                <img
                    src="/images/logo.png"
                    alt=""
                    class="size-7 object-contain"
                />
                <span>School CMS</span>
            </Link>
            <button
                type="button"
                class="rounded-lg p-2 hover:bg-muted"
                aria-label="Toggle menu"
                @click="open = !open"
            >
                <component :is="open ? X : Menu" class="size-5" />
            </button>
        </div>

        <div class="lg:flex">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 z-40 w-64 shrink-0 border-e border-border bg-background transition-transform lg:static lg:translate-x-0"
                :class="open ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-full flex-col">
                    <Link
                        href="/admin"
                        class="hidden items-center gap-2.5 border-b border-border px-6 py-5 lg:flex"
                    >
                        <img
                            src="/images/logo.png"
                            alt=""
                            class="size-9 object-contain"
                        />
                        <div class="leading-tight">
                            <div class="font-semibold">School CMS</div>
                            <div class="text-xs text-muted-foreground">
                                Hithaadhoo School
                            </div>
                        </div>
                    </Link>

                    <nav class="flex-1 space-y-1 p-4">
                        <template v-for="item in visibleNav" :key="item.href">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                                :class="
                                    isActive(item.match)
                                        ? 'bg-brand text-brand-foreground shadow-sm'
                                        : 'text-foreground/70 hover:bg-muted hover:text-foreground'
                                "
                                @click="open = false"
                            >
                                <component :is="item.icon" class="size-5" />
                                {{ item.title }}
                            </Link>

                            <div
                                v-if="item.children && isActive(item.match)"
                                class="ms-4 mt-1 space-y-0.5 border-s border-border ps-3"
                            >
                                <Link
                                    v-for="child in item.children"
                                    :key="child.key"
                                    :href="`/admin/content/${child.key}/edit`"
                                    class="block rounded-lg px-3 py-2 text-sm transition"
                                    :class="
                                        isSection(child.key)
                                            ? 'bg-brand-muted font-medium text-brand'
                                            : 'text-foreground/60 hover:bg-muted hover:text-foreground'
                                    "
                                    @click="open = false"
                                >
                                    {{ child.title }}
                                </Link>
                            </div>
                        </template>

                        <a
                            href="/"
                            target="_blank"
                            rel="noopener"
                            class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-foreground/70 transition hover:bg-muted hover:text-foreground"
                        >
                            <ExternalLink class="size-5" />
                            View website
                        </a>

                        <div class="mt-4 border-t border-border pt-4">
                            <div
                                class="px-3 pb-1 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Platform
                            </div>
                            <Link
                                v-for="item in platformNav"
                                :key="item.title"
                                :href="item.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                                :class="
                                    isActive(item.match)
                                        ? 'bg-brand text-brand-foreground shadow-sm'
                                        : 'text-foreground/70 hover:bg-muted hover:text-foreground'
                                "
                                @click="open = false"
                            >
                                <component :is="item.icon" class="size-5" />
                                {{ item.title }}
                            </Link>
                        </div>
                    </nav>

                    <div class="border-t border-border p-4">
                        <div class="mb-3 px-1">
                            <div class="truncate text-sm font-medium">
                                {{ user?.name }}
                            </div>
                            <div class="truncate text-xs text-muted-foreground">
                                {{ user?.email }}
                            </div>
                        </div>
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-foreground/70 transition hover:bg-muted hover:text-foreground"
                            @click="logout"
                        >
                            <LogOut class="size-5" />
                            Log out
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Backdrop for mobile -->
            <div
                v-if="open"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
                @click="open = false"
            />

            <!-- Main content -->
            <main class="min-w-0 flex-1">
                <nav
                    v-if="breadcrumbs.length"
                    class="flex flex-wrap items-center gap-1.5 border-b border-border bg-background px-4 py-2.5 text-sm sm:px-6 lg:px-10"
                    aria-label="Breadcrumb"
                >
                    <Link
                        href="/admin"
                        class="text-brand transition hover:underline"
                        >CMS</Link
                    >
                    <template v-for="crumb in breadcrumbs" :key="crumb.title">
                        <span class="text-muted-foreground">/</span>
                        <Link :href="crumb.href" class="font-medium">{{
                            crumb.title
                        }}</Link>
                    </template>
                </nav>

                <div class="w-full px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
                    <slot />
                </div>
            </main>
        </div>

        <Toaster />
    </div>
</template>
