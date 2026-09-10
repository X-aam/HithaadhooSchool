<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import type { FieldDef } from '@/components/admin/schema';
import SchemaField from '@/components/admin/SchemaField.vue';
import { school } from '@/lib/sampleData';

const props = defineProps<{ section: string; label: string; value: any }>();

const form = useForm<{ value: Record<string, any> }>({
    value: props.value ?? clone(school as unknown as Record<string, any>),
});

const statsFields: FieldDef[] = [
    { key: 'established', type: 'number', label: 'Year established' },
    { key: 'students', type: 'number', label: 'Students' },
    { key: 'teachers', type: 'number', label: 'Teachers' },
];

const textFields: FieldDef[] = [
    { key: 'motto', type: 'bilingual', label: 'Motto' },
    { key: 'welcome', type: 'bilingualText', label: 'Welcome message' },
    { key: 'mission', type: 'bilingualText', label: 'Mission' },
    { key: 'vision', type: 'bilingualText', label: 'Vision' },
];

const principalFields: FieldDef[] = [
    { key: 'name', type: 'bilingual', label: 'Name' },
    { key: 'title', type: 'bilingual', label: 'Title' },
    { key: 'photo', type: 'image', label: 'Photo URL' },
];

const contactFields: FieldDef[] = [
    { key: 'address', type: 'bilingual', label: 'Address' },
    { key: 'phone', type: 'text', label: 'Phone' },
    { key: 'email', type: 'text', label: 'Email' },
    { key: 'officeHours', type: 'bilingual', label: 'Office hours' },
    { key: 'mapLat', type: 'number', step: 'any', label: 'Map latitude' },
    { key: 'mapLng', type: 'number', step: 'any', label: 'Map longitude' },
];

const socialFields: FieldDef[] = [
    { key: 'facebook', type: 'text', label: 'Facebook URL' },
    { key: 'instagram', type: 'text', label: 'Instagram URL' },
    { key: 'youtube', type: 'text', label: 'YouTube URL' },
    { key: 'x', type: 'text', label: 'X (Twitter) URL' },
];

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}
</script>

<template>
    <ContentEditorShell
        :section="section"
        :label="label"
        :processing="form.processing"
        @save="save"
    >
        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Key figures
            </h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <SchemaField
                    v-for="f in statsFields"
                    :key="f.key"
                    :obj="form.value"
                    :field="f"
                />
            </div>
        </section>

        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                About the school
            </h2>
            <div class="grid gap-4">
                <SchemaField
                    v-for="f in textFields"
                    :key="f.key"
                    :obj="form.value"
                    :field="f"
                />
            </div>
        </section>

        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Principal
            </h2>
            <div class="grid gap-4">
                <SchemaField
                    v-for="f in principalFields"
                    :key="f.key"
                    :obj="form.value.principal"
                    :field="f"
                />
            </div>
        </section>

        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Contact
            </h2>
            <div class="grid gap-4">
                <SchemaField
                    v-for="f in contactFields"
                    :key="f.key"
                    :obj="form.value.contact"
                    :field="f"
                />
            </div>
        </section>

        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Social links
            </h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <SchemaField
                    v-for="f in socialFields"
                    :key="f.key"
                    :obj="form.value.contact.social"
                    :field="f"
                />
            </div>
        </section>
    </ContentEditorShell>
</template>
