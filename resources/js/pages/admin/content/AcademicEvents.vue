<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AcademicCalendarEditor from '@/components/admin/AcademicCalendarEditor.vue';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import { academicEvents } from '@/lib/sampleData';

const props = defineProps<{ section: string; label: string; value: any }>();

const form = useForm<{ value: Record<string, any>[] }>({
    value: props.value ?? clone(academicEvents as unknown as Record<string, any>[]),
});

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}
</script>

<template>
    <ContentEditorShell :section="section" :label="label" :processing="form.processing" @save="save">
        <AcademicCalendarEditor :events="form.value" />
    </ContentEditorShell>
</template>
