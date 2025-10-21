<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Briefcase, Code, FileText, Folder, FolderDot, GraduationCap, LayoutGrid, MessageSquare, Tags, UserPen } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Projects',
        href: '/projects',
        icon: FolderDot,
    },
    {
        title: 'Skills',
        href: '/technologies',
        icon: Code,
    },
    {
        title: "Developer' Profile",
        href: '/dev-profile/edit',
        icon: UserPen,
    },
    {
        title: 'Work Experience',
        href: '/work-experiences',
        icon: Briefcase,
    },
    {
        title: 'Certifications',
        href: '/certifications',
        icon: GraduationCap,
    },
    {
        title: 'Blog',
        icon: FileText,
        href: '',
        items: [
            {
                title: 'Posts',
                href: '/posts',
                icon: FileText,
            },
            {
                title: 'Categories',
                href: '/categories',
                icon: Folder,
            },
            {
                title: 'Tags',
                href: '/tags',
                icon: Tags,
            },
            {
                title: 'Comments',
                href: '/comments',
                icon: MessageSquare,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
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
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
