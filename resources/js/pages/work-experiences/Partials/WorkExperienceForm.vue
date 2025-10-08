<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// UI components (adjust paths as needed)
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Experience = {
    id?: number;
    job_title: string;
    company_name: string;
    company_website?: string | null;
    description: string;
    start_date: string;
    end_date: string;
};

const props = defineProps<{
    experience?: Partial<Experience>;
    submitRoute: string;
    method?: 'post' | 'put' | 'patch';
}>();

const form = useForm<Experience>({
    job_title: props.experience?.job_title ?? '',
    company_name: props.experience?.company_name ?? '',
    company_website: props.experience?.company_website ?? '',
    description: props.experience?.description ?? '',
    start_date: props.experience?.start_date ?? '',
    end_date: props.experience?.end_date ?? '',
});

const isUpdating = computed(() => props.method && props.method !== 'post');

function submit() {
    if (props.method && props.method !== 'post') {
        form.submit(props.method.toUpperCase(), props.submitRoute);
    } else {
        form.post(props.submitRoute);
    }
}
</script>

<template>
    <div class="container mx-auto p-4">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <Label for="job_title">Job Title</Label>
                    <Input id="job_title" v-model="form.job_title" />
                    <div v-if="form.errors.job_title" class="mt-1 text-sm text-red-500">{{ form.errors.job_title }}</div>
                </div>

                <div>
                    <Label for="company_name">Company Name</Label>
                    <Input id="company_name" v-model="form.company_name" />
                    <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-500">{{ form.errors.company_name }}</div>
                </div>

                <div>
                    <Label for="company_website">Company Website</Label>
                    <Input id="company_website" v-model="form.company_website" placeholder="https://example.com" />
                    <div v-if="form.errors.company_website" class="mt-1 text-sm text-red-500">{{ form.errors.company_website }}</div>
                </div>
            </div>

            <div>
                <Label for="description">Description</Label>
                <Textarea id="description" v-model="form.description" rows="6" />
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <Label for="start_date">Start Date</Label>
                    <Input id="start_date" type="date" v-model="form.start_date" />
                    <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-500">{{ form.errors.start_date }}</div>
                </div>

                <div>
                    <Label for="end_date">End Date</Label>
                    <Input id="end_date" type="date" v-model="form.end_date" />
                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-500">{{ form.errors.end_date }}</div>
                </div>
            </div>

            <div class="pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ isUpdating ? 'Update' : 'Create' }}
                </Button>
            </div>
        </form>
    </div>
</template>
