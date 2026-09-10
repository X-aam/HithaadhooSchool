<script setup lang="ts">
import { ref } from 'vue';
import { uploadImage } from '@/lib/uploadImage';
import type { FieldDef } from './schema';

const props = defineProps<{ obj: Record<string, any>; field: FieldDef }>();

const inputClass =
    'w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30';

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const uploadError = ref('');

async function onFilePicked(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
return;
}

    uploading.value = true;
    uploadError.value = '';

    try {
        props.obj[props.field.key] = await uploadImage(file);
    } catch {
        uploadError.value = 'Upload failed. Use a JPG, PNG, WebP or GIF under 5 MB.';
    } finally {
        uploading.value = false;

        if (fileInput.value) {
fileInput.value.value = '';
}
    }
}

/** Ensure bilingual fields are always an { en, dv } object before binding. */
if (props.field.type === 'bilingual' || props.field.type === 'bilingualText') {
    const v = props.obj[props.field.key];

    if (!v || typeof v !== 'object') {
        props.obj[props.field.key] = { en: '', dv: '' };
    }
}
</script>

<template>
    <div>
        <label class="mb-1.5 block text-sm font-medium">{{ field.label }}</label>

        <input v-if="field.type === 'text'" v-model="obj[field.key]" type="text" :placeholder="field.placeholder" :class="inputClass" />

        <div v-else-if="field.type === 'image'" class="flex gap-2">
            <input v-model="obj[field.key]" type="text" :placeholder="field.placeholder" :class="inputClass" />
            <button
                type="button"
                class="shrink-0 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted disabled:opacity-50"
                :disabled="uploading"
                @click="fileInput?.click()"
            >
                {{ uploading ? 'Uploading…' : 'Upload' }}
            </button>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onFilePicked" />
        </div>

        <textarea v-else-if="field.type === 'textarea'" v-model="obj[field.key]" rows="4" :class="inputClass"></textarea>

        <input v-else-if="field.type === 'number'" v-model.number="obj[field.key]" type="number" :step="field.step" :class="inputClass" />

        <input v-else-if="field.type === 'date'" v-model="obj[field.key]" type="date" :class="inputClass" />

        <select v-else-if="field.type === 'select'" v-model="obj[field.key]" :class="inputClass">
            <option v-for="o in field.options" :key="String(o.value)" :value="o.value">{{ o.label }}</option>
        </select>

        <div v-else-if="field.type === 'bilingual'" class="grid gap-2 sm:grid-cols-2">
            <input v-model="obj[field.key].en" type="text" placeholder="English" :class="inputClass" />
            <input v-model="obj[field.key].dv" dir="rtl" type="text" placeholder="ދިވެހި" :class="[inputClass, 'font-thaana']" />
        </div>

        <div v-else-if="field.type === 'bilingualText'" class="grid gap-2 sm:grid-cols-2">
            <textarea v-model="obj[field.key].en" rows="3" placeholder="English" :class="inputClass"></textarea>
            <textarea v-model="obj[field.key].dv" dir="rtl" rows="3" placeholder="ދިވެހި" :class="[inputClass, 'font-thaana']"></textarea>
        </div>

        <p v-if="uploadError" class="mt-1 text-xs text-destructive">{{ uploadError }}</p>
        <p v-if="field.help" class="mt-1 text-xs text-muted-foreground">{{ field.help }}</p>

        <img
            v-if="field.type === 'image' && obj[field.key]"
            :src="obj[field.key]"
            alt=""
            class="mt-2 h-24 rounded-lg border border-border object-cover"
        />
    </div>
</template>
