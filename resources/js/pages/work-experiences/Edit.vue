<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import WorkExperienceForm from './Partials/WorkExperienceForm.vue';

const props = defineProps<{
    experience: {
        id: number;
        job_title: string;
        company_name: string;
        company_website?: string | null;
        description: string;
        start_date: string;
        end_date: string;
    };
}>();
</script>

<template>
    <Head :title="`Edit: ${props.experience.job_title}`" />
    <AppLayout :breadcrumbs="[{ label: 'Work Experience', href: route('work-experiences.index') }, { label: `Edit: ${props.experience.job_title}` }]">
        <div class="space-y-4 p-4">
            <h1 class="text-xl font-semibold">Edit Work Experience</h1>
            <WorkExperienceForm :experience="props.experience" :submit-route="route('work-experiences.update', props.experience.id)" method="put" />
        </div>
    </AppLayout>
</template>
