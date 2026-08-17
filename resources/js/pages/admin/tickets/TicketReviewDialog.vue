<script setup>
import { ref, watch } from 'vue';
import api from '@/services/api';

const props = defineProps({
    ticket: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    'update:visible',
    'approved',
    'rejected',
]);

const visible = defineModel('visible', {
    type: Boolean,
    default: false,
});

const loading = ref(false);
const action = ref(null);
const error = ref('');
const comment = ref(null);
const commentError = ref('');


const approveTicket = async () => {
    await updateTicketStatus('approve');
};

const rejectTicket = async () => {

    await updateTicketStatus('reject');
};

const updateTicketStatus = async (status) => {
    if (!props.ticket) {
        return;
    }

    loading.value = true;
    action.value = status;
    error.value = '';

    try {
        await api.post(`/api/admin/tickets/${props.ticket.id}/${status}`, {
            status,
            comment: comment.value,
        });

        visible.value = false;

        if (status === 'approve') {
            emit('approved', props.ticket);
        } else {
            emit('rejected', props.ticket);
        }
    } catch (err) {
        console.error('Failed to update ticket:', err);

        error.value = 'Failed to update ticket.';
    } finally {
        loading.value = false;
        action.value = null;
    }
};

const formatDate = (date) => {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleString();
};
</script>

<template>
    <Dialog v-model:visible="visible" modal header="Ticket Details" :style="{ width: '45rem' }">
        <div v-if="ticket" class="flex flex-col gap-5">
            <Message v-if="error" severity="error" :closable="false">
                {{ error }}
            </Message>

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
                        User
                    </label>

                    <InputText :modelValue="ticket.user?.name" class="w-full" readonly />
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Created At
                    </label>

                    <InputText :modelValue="formatDate(ticket.created_at)" class="w-full" readonly />
                </div>
            </div>

            <div>
                <label class="block font-medium mb-2">
                    Attachment
                </label>

                <Button label="View Attachment" icon="pi pi-paperclip" outlined severity="secondary" as="a" :href="ticket.attachment?.url" target="_blank"/>
            </div>

            <div>
                <label class="block font-medium mb-2">
                    Comment
                </label>

                <Textarea v-model="comment" rows="5" class="w-full"
                    placeholder="Explain why this ticket is being..." :readonly="!ticket.can_reject"/>

                <Message v-if="commentError" severity="error" :closable="false" class="mt-3">
                    {{ commentError }}
                </Message>
            </div>
        </div>

        <template #footer>
            <Button label="Reject" icon="pi pi-times" severity="danger" outlined :loading="action === 'rejected'"
                :disabled="loading || !ticket.can_reject" @click="rejectTicket" />

            <Button label="Approve" icon="pi pi-check" severity="success" :loading="action === 'approved'"
                :disabled="loading || !ticket.can_approve" @click="approveTicket" />
        </template>
    </Dialog>
</template>