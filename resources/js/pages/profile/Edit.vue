<template>
    <Head title="Edit Profile" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <Alert v-if="page.props.flash?.message" class="mb-6">
                <CheckCircle class="h-4 w-4" />
                <AlertTitle>Success</AlertTitle>
                <AlertDescription>{{ page.props.flash.message }}</AlertDescription>
            </Alert>

            <form @submit.prevent="submit">
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>About Me</CardTitle>
                        <CardDescription>Your personal information and bio</CardDescription>
                    </CardHeader>
                    <CardContent class="mb-6 grid items-start gap-6 md:grid-cols-2">
                        <div>
                            <Label for="job_title" class="mb-2">Job Title *</Label>
                            <Input
                                id="job_title"
                                v-model="form.job_title"
                                type="text"
                                placeholder="e.g., Full Stack Developer"
                                :class="{ 'border-red-500': form.errors.job_title }"
                            />
                            <p v-if="form.errors.job_title" class="mt-1 text-sm text-red-500">
                                {{ form.errors.job_title }}
                            </p>
                        </div>

                        <div>
                            <Label for="short_bio" class="mb-2">
                                Short Bio *
                                <span class="text-xs text-muted-foreground">({{ form.short_bio?.length || 0 }}/500)</span>
                            </Label>
                            <Textarea
                                id="short_bio"
                                v-model="form.short_bio"
                                rows="3"
                                maxlength="500"
                                placeholder="A brief introduction about yourself (2-3 sentences)"
                                :class="{ 'border-red-500': form.errors.short_bio }"
                            />
                            <p v-if="form.errors.short_bio" class="mt-1 text-sm text-red-500">
                                {{ form.errors.short_bio }}
                            </p>
                        </div>

                        <div>
                            <Label for="long_description" class="mb-2">My Story *</Label>
                            <Textarea
                                id="long_description"
                                v-model="form.long_description"
                                rows="10"
                                placeholder="Tell your story, your journey, your experiences..."
                                :class="{ 'border-red-500': form.errors.long_description }"
                            />
                            <p v-if="form.errors.long_description" class="mt-1 text-sm text-red-500">
                                {{ form.errors.long_description }}
                            </p>
                        </div>

                        <div>
                            <Label for="professional_summary" class="mb-2">Professional Summary</Label>
                            <Textarea
                                id="professional_summary"
                                v-model="form.professional_summary"
                                rows="10"
                                :class="{ 'border-red-500': form.errors.professional_summary }"
                            />
                            <p v-if="form.errors.professional_summary" class="mt-1 text-sm text-red-500">
                                {{ form.errors.professional_summary }}
                            </p>
                        </div>

                        <div>
                            <Label for="languages" class="mb-2">Languages (comma separated)</Label>
                            <Input id="languages" v-model="languagesInput" type="text" placeholder="English, Greek" />
                            <p class="mt-1 text-xs text-muted-foreground">Separate languages with commas</p>
                        </div>

                        <div>
                            <Label for="years_experience" class="mb-2">Years of Experience</Label>
                            <Input id="years_experience" v-model="form.years_experience" type="text" placeholder="5" />
                            <p v-if="form.errors.years_experience" class="mt-1 text-sm text-red-500">
                                {{ form.errors.years_experience }}
                            </p>
                        </div>

                        <div>
                            <Label>Profile Photo</Label>
                            <div class="mt-2 flex items-center gap-4">
                                <div v-if="profile.avatar" class="relative">
                                    <img
                                        :src="avatarPreview || profile.avatar.url"
                                        alt="Avatar"
                                        class="h-20 w-20 rounded-full border-2 object-cover"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <Button type="button" variant="outline" size="sm" @click="$refs.avatarInput.click()">
                                        <Upload class="mr-2 h-4 w-4" />
                                        {{ profile.avatar ? 'Change' : 'Upload' }}
                                    </Button>
                                    <Button
                                        v-if="profile.avatar"
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteAvatar"
                                        :disabled="deletingAvatar"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="handleAvatarUpload" />
                            <p class="mt-1 text-xs text-muted-foreground">JPG, PNG, or WebP. Max 2MB.</p>
                        </div>

                        <div>
                            <Label>Resume/CV</Label>
                            <div class="mt-2 flex items-center gap-2">
                                <Button type="button" variant="outline" size="sm" @click="$refs.resumeInput.click()">
                                    <FileText class="mr-2 h-4 w-4" />
                                    {{ profile.resume ? 'Change Resume' : 'Upload Resume' }}
                                </Button>
                                <Button
                                    v-if="profile.resume"
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    @click="deleteResume"
                                    :disabled="deletingResume"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                                <span v-if="profile.resume" class="text-sm text-muted-foreground">
                                    <a :href="profile.resume.url" target="_blank">{{ profile.resume.original_name }}</a>
                                </span>
                            </div>
                            <input ref="resumeInput" type="file" accept=".pdf" class="hidden" @change="handleResumeUpload" />
                            <p class="mt-1 text-xs text-muted-foreground">PDF only. Max 5MB.</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                        <CardDescription>How people can reach you</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Email -->
                            <div>
                                <Label for="email" class="mb-2">Email *</Label>
                                <Input id="email" v-model="form.email" type="email" :class="{ 'border-red-500': form.errors.email }" />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <Label for="phone" class="mb-2">Phone</Label>
                                <Input id="phone" v-model="form.phone" type="tel" placeholder="+30 123 456 7890" />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div>
                                <Label for="location" class="mb-2">Location</Label>
                                <Input id="location" v-model="form.location" type="text" placeholder="Athens, Greece" />
                                <p v-if="form.errors.location" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.location }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Social Media</CardTitle>
                        <CardDescription>Your social media profiles</CardDescription>
                    </CardHeader>
                    <CardContent class="mb-6 grid items-start gap-6 space-x-6 md:grid-cols-2">
                        <!-- GitHub -->
                        <div>
                            <Label for="github_url" class="mb-2">
                                <Github class="mr-1 inline h-4 w-4" />
                                GitHub
                            </Label>
                            <Input id="github_url" v-model="form.github_url" type="url" placeholder="https://github.com/yourusername" />
                            <p v-if="form.errors.github_url" class="mt-1 text-sm text-red-500">
                                {{ form.errors.github_url }}
                            </p>
                        </div>

                        <div>
                            <Label for="linkedin_url" class="mb-2">
                                <Linkedin class="mr-1 inline h-4 w-4" />
                                LinkedIn
                            </Label>
                            <Input id="linkedin_url" v-model="form.linkedin_url" type="url" placeholder="https://linkedin.com/in/yourusername" />
                            <p v-if="form.errors.linkedin_url" class="mt-1 text-sm text-red-500">
                                {{ form.errors.linkedin_url }}
                            </p>
                        </div>

                        <div>
                            <Label for="website_url" class="mb-2">
                                <Globe class="mr-1 inline h-4 w-4" />
                                Website
                            </Label>
                            <Input id="website_url" v-model="form.website_url" type="url" placeholder="https://yourwebsite.com" />
                            <p v-if="form.errors.website_url" class="mt-1 text-sm text-red-500">
                                {{ form.errors.website_url }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Education</CardTitle>
                        <CardDescription>Your Academic Education</CardDescription>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label for="university" class="mb-2"> University </Label>
                            <Input id="university" v-model="form.university" type="text" placeholder="MIT" />
                            <p v-if="form.errors.university" class="mt-1 text-sm text-red-500">
                                {{ form.errors.university }}
                            </p>
                        </div>
                        <div>
                            <Label for="degree" class="mb-2"> Degree </Label>
                            <Input id="degree" v-model="form.degree" type="text" placeholder="Master's degree" />
                            <p v-if="form.errors.degree" class="mt-1 text-sm text-red-500">
                                {{ form.errors.degree }}
                            </p>
                        </div>
                        <div>
                            <Label for="start_date_uni" class="mb-2"> Start date </Label>
                            <Input id="start_date_uni" v-model="form.start_date_uni" type="date" />
                            <p v-if="form.errors.start_date_uni" class="mt-1 text-sm text-red-500">
                                {{ form.errors.start_date_uni }}
                            </p>
                        </div>
                        <div>
                            <Label for="end_date_uni" class="mb-2"> End date </Label>
                            <Input id="end_date_uni" v-model="form.end_date_uni" type="date" />
                            <p v-if="form.errors.end_date_uni" class="mt-1 text-sm text-red-500">
                                {{ form.errors.end_date_uni }}
                            </p>
                        </div>
                        <div>
                            <Label for="degree_url" class="mb-2"> Degree url </Label>
                            <Input id="degree_url" v-model="form.degree_url" type="url" />
                            <p v-if="form.errors.degree_url" class="mt-1 text-sm text-red-500">
                                {{ form.errors.degree_url }}
                            </p>
                        </div>
                        <div>
                            <Label for="diploma_thesis_url" class="mb-2"> Diploma thesis url </Label>
                            <Input id="diploma_thesis_url" v-model="form.diploma_thesis_url" type="url" />
                            <p v-if="form.errors.diploma_thesis_url" class="mt-1 text-sm text-red-500">
                                {{ form.errors.diploma_thesis_url }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Saving...' : 'Save Profile' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle, FileText, Github, Globe, Linkedin, Save, Trash2, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    profile: Object,
});

const page = usePage();

const breadcrumbs = [{ title: 'Profile', href: '/dev-profile/edit' }, { title: 'Edit' }];

console.log(props);
const form = useForm({
    job_title: props.profile.job_title || '',
    short_bio: props.profile.short_bio || '',
    long_description: props.profile.long_description || '',
    professional_summary: props.profile.professional_summary || '',
    email: props.profile.email || '',
    phone: props.profile.phone || '',
    location: props.profile.location || '',
    github_url: props.profile.github_url || '',
    linkedin_url: props.profile.linkedin_url || '',
    twitter_url: props.profile.twitter_url || '',
    instagram_url: props.profile.instagram_url || '',
    website_url: props.profile.website_url || '',
    years_experience: props.profile.years_experience || '',
    languages: props.profile.languages || '',
    university: props.profile.university || '',
    degree: props.profile.degree || '',
    start_date_uni: props.profile.start_date_uni || null,
    end_date_uni: props.profile.end_date_uni || null,
    degree_url: props.profile.degree_url || '',
    diploma_thesis_url: props.profile.diploma_thesis_url || '',
});

const languagesInput = computed({
    get: () => (form.languages || []).join(', '),
    set: (value) => {
        form.languages = value
            .split(',')
            .map((l) => l.trim())
            .filter(Boolean);
    },
});

const avatarPreview = ref(null);
const avatarInput = ref(null);
const resumeInput = ref(null);
const deletingAvatar = ref(false);
const deletingResume = ref(false);

// Handle avatar upload (separate request)
const handleAvatarUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Show preview
    avatarPreview.value = URL.createObjectURL(file);

    // Upload immediately
    const avatarForm = new FormData();
    avatarForm.append('avatar', file);

    router.post(route('dev_profile.avatar.upload'), avatarForm, {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
        },
        onError: (errors) => {
            avatarPreview.value = null;
            alert(errors.avatar || 'Failed to upload avatar');
        },
    });

    // Reset input
    event.target.value = '';
};

// Handle resume upload (separate request)
const handleResumeUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Upload immediately
    const resumeForm = new FormData();
    resumeForm.append('resume', file);

    router.post(route('dev_profile.resume.upload'), resumeForm, {
        preserveScroll: true,
        onError: (errors) => {
            alert(errors.resume || 'Failed to upload resume');
        },
    });

    // Reset input
    event.target.value = '';
};

// Delete avatar
const deleteAvatar = () => {
    if (!confirm('Are you sure you want to delete your profile photo?')) return;

    deletingAvatar.value = true;
    router.delete(route('dev_profile.avatar.delete'), {
        preserveScroll: true,
        onFinish: () => {
            deletingAvatar.value = false;
        },
    });
};

// Delete resume
const deleteResume = () => {
    if (!confirm('Are you sure you want to delete your resume?')) return;

    deletingResume.value = true;
    router.delete(route('dev_profile.resume.delete'), {
        preserveScroll: true,
        onFinish: () => {
            deletingResume.value = false;
        },
    });
};

const submit = () => {
    form.post(route('dev_profile.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>
