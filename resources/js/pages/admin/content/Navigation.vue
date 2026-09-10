<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import NavMenuEditor from '@/components/admin/NavMenuEditor.vue';
import { clone } from '@/components/admin/schema';
import { defaultNavigation } from '@/lib/siteNavigation';

const props = defineProps<{ section: string; label: string; value: any }>();

const form = useForm<{ value: Record<string, any>[] }>({
    value: props.value ?? clone(defaultNavigation() as unknown as Record<string, any>[]),
});

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}
</script>

<template>
    <ContentEditorShell :section="section" :label="label" :processing="form.processing" @save="save">
        <NavMenuEditor :items="form.value" />
    </ContentEditorShell>
</template>
