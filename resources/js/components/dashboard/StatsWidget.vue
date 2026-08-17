<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    value: {
        type: [Number, String],
        default: 0,
    },

    icon: {
        type: String,
        required: true,
    },

    iconClass: {
        type: String,
        default: '',
    },

    iconBgClass: {
        type: String,
        default: '',
    },

    description: {
        type: String,
        default: '',
    },

    descriptionClass: {
        type: String,
        default: 'text-muted-color',
    },
});
</script>

<template>
    <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
            <div class="flex justify-between mb-4">
                <div>
                    <span class="block text-muted-color font-medium mb-4">
                        {{ title }}
                    </span>

                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">
                        {{ value }}
                    </div>
                </div>

                <div
                    class="flex items-center justify-center rounded-border"
                    :class="iconBgClass"
                    style="width: 2.5rem; height: 2.5rem"
                >
                    <i
                        class="pi !text-xl"
                        :class="[icon, iconClass]"
                    ></i>
                </div>
            </div>

            <span
                v-if="description"
                :class="descriptionClass"
            >
                {{ description }}
            </span>
        </div>
    </div>
</template>