<script setup>
import { ref } from 'vue';
import api from '@/services/api';

const emit = defineEmits([
    'created',
]);

const visible = defineModel('visible', {
    type: Boolean,
    default: false,
});

const title = ref('');
const description = ref('');
const attachment = ref(null);

const loading = ref(false);
const error = ref('');

const resetForm = () => {
    title.value = '';
    description.value = '';
    attachment.value = null;
    error.value = '';
};

const handleFileSelect = (event) => {
    attachment.value = event.files?.[0] ?? null;
};

const createTicket = async () => {
    error.value = '';

    if (!title.value || !description.value || !attachment.value) {
        error.value = 'Please complete all required fields.';
        return;
    }

    loading.value = true;

    try {
        const formData = new FormData();

        formData.append('title', title.value);
        formData.append('description', description.value);
        formData.append('attachment', attachment.value);

        const response = await api.post(
            '/api/tickets',
            formData
        );

        visible.value = false;

        emit('created', response.data.data);

        resetForm();
    } catch (err) {
        console.error('Failed to create ticket:', err);

        if (err.response?.status === 422) {
            error.value = 'Please check the submitted information.';
        } else {
            error.value = 'Failed to create ticket.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        header="Create Ticket"
        :style="{ width: '40rem' }"
        @hide="resetForm"
    >
        <div class="flex flex-col gap-5">
            <Message
                v-if="error"
                severity="error"
                :closable="false"
            >
                {{ error }}
            </Message>

            <div>
                <label
                    for="ticket-title"
                    class="block font-medium mb-2"
                >
                    Title
                </label>

                <InputText
                    id="ticket-title"
                    v-model="title"
                    class="w-full"
                />
            </div>

            <div>
                <label
                    for="ticket-description"
                    class="block font-medium mb-2"
                >
                    Description
                </label>

                <Textarea
                    id="ticket-description"
                    v-model="description"
                    rows="6"
                    class="w-full"
                />
            </div>

            <div>
                <label class="block font-medium mb-2">
                    Attachment
                </label>

                <FileUpload
                    mode="basic"
                    name="attachment"
                    :auto="false"
                    chooseLabel="Choose File"
                    @select="handleFileSelect"
                />
            </div>
        </div>

        <template #footer>
            <Button
                label="Cancel"
                severity="secondary"
                text
                :disabled="loading"
                @click="visible = false"
            />

            <Button
                label="Create Ticket"
                icon="pi pi-check"
                :loading="loading"
                @click="createTicket"
            />
        </template>
    </Dialog>
</template>