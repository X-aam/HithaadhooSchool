<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Fades / slides its content in the first time it scrolls into view.
 * Respects `prefers-reduced-motion` via CSS (see app.css .reveal rules).
 */
const props = withDefaults(
    defineProps<{
        as?: string;
        delay?: number;
    }>(),
    { as: 'div', delay: 0 },
);

const el = ref<HTMLElement | null>(null);
const visible = ref(false);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') {
        visible.value = true;

        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setTimeout(() => (visible.value = true), props.delay);
                    observer?.disconnect();
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    if (el.value) {
        observer.observe(el.value);
    }
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <component
        :is="props.as"
        ref="el"
        class="reveal"
        :class="{ 'is-visible': visible }"
    >
        <slot />
    </component>
</template>
