<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    ticket: {
        type: Object,
        default: null,
    },
});

const visible = defineModel('visible', {
    type: Boolean,
    default: false,
});

</script>

<template>
    <Dialog v-model:visible="visible" modal header="Ticket Details" :style="{ width: '45rem' }">
        <div v-if="ticket" class="flex flex-col gap-5">
            <div>
                <label class="block font-medium mb-2">
                    Title
                </label>

                <InputText :modelValue="ticket.title" class="w-full" readonly />
            </div>

            <div>
                <label class="block font-medium mb-2">
                    Description
                </label>

                <Textarea :modelValue="ticket.description" rows="7" class="w-full" readonly />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-2">
                        Created At
                    </label>

                    <InputText :modelValue="ticket.created_at" class="w-full" readonly />
                </div>
            </div>

            <div>
                <label class="block font-medium mb-2">
                    Attachment
                </label>

                <Button label="View Attachment" icon="pi pi-paperclip" outlined severity="secondary" as="a" :href="ticket.attachment?.url" target="_blank"/>
            </div>

        </div>
    </Dialog>
</template>