<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1',
    },
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        48: 'dropdown-menu-width-48',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'dropdown-menu-start';
    } else if (props.align === 'right') {
        return 'dropdown-menu-end';
    } else {
        return '';
    }
});

const open = ref(false);
</script>

<template>
    <div class="position-relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="position-fixed top-0 start-0 w-100 h-100"
            style="z-index: 40"
            @click="open = false"
        ></div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="open"
                class="dropdown-menu shadow mt-2"
                :class="[widthClass, alignmentClasses]"
                style="display: block"
                @click="open = false"
            >
                <div :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-menu-width-48 {
    min-width: 12rem;
}
</style>
