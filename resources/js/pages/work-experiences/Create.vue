<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import WorkExperienceForm from './Partials/WorkExperienceForm.vue';
</script>

<template>
    <Head title="Create Work Experience" />
    <AppLayout :breadcrumbs="[{ label: 'Work Experience', href: route('work-experiences.index') }, { label: 'Create' }]">
        <div class="space-y-4 p-4">
            <h1 class="text-xl font-semibold">Create Work Experience</h1>
            <WorkExperienceForm :submit-route="route('work-experiences.store')" method="post" />
        </div>
    </AppLayout>
</template>
