<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileText, FolderGit2, LayoutDashboard, LayoutGrid, Megaphone, Newspaper, Users } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
    },
]);

const cmsNavItems = computed<NavItem[]>(() => {
    const role = (page.props.auth?.user as { role?: string } | undefined)?.role ?? 'author';
    const canContent = role === 'admin' || role === 'editor';
    const canUsers = role === 'admin';

    return [
        { title: 'CMS dashboard', href: '/admin', icon: LayoutDashboard },
        { title: 'News & Blog', href: '/admin/news', icon: Newspaper },
        ...(canContent
            ? [
                  { title: 'Announcements', href: '/admin/announcements', icon: Megaphone },
                  { title: 'Pages', href: '/admin/content', icon: FileText },
              ]
            : []),
        ...(canUsers ? [{ title: 'Users', href: '/admin/users', icon: Users }] : []),
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="cmsNavItems" label="Content management" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
