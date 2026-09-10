<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import StaffNodeEditor from '@/components/admin/StaffNodeEditor.vue';
import { orgChart } from '@/lib/sampleData';

const props = defineProps<{ section: string; label: string; value: any }>();

const form = useForm<{ value: Record<string, any> }>({
    value: props.value ?? clone(orgChart as unknown as Record<string, any>),
});

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
        <p
            class="rounded-xl border border-border bg-muted/30 px-4 py-3 text-sm text-muted-foreground"
        >
            The person at the top is the head of the school. Use the
            <strong>+</strong> button to add someone reporting to them, and
            <strong>More</strong> for a photo and bio. The number badge counts
            everyone beneath a person.
        </p>
        <StaffNodeEditor :node="form.value" />
    </ContentEditorShell>
</template>
