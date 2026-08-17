<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

import CreateTicketDialog from './CreateDialog.vue';
import ViewTicketDialog from './ViewDialog.vue';

const router = useRouter();

const tickets = ref([]);
const loading = ref(false);
const error = ref('');

const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
});

const createDialogVisible = ref(false);
const saving = ref(false);
const createError = ref('');

const selectedTicket = ref(null);
const viewDialogVisible = ref(false);


const statuses = [
    {
        label: 'pending_review',
        value: 'Pending Review',
    },
    {
        label: 'pending_level_two',
        value: 'Pending Level Two',
    },
    {
        label: 'rejected',
        value: 'Rejected',
    },
    {
        label: 'sending',
        value: 'Sending',
    },
    {
        label: 'send_failed',
        value: 'Send Failed',
    },
    {
        label: 'sent',
        value: 'Sent',
    }
];

const createTicketDialogVisible = ref(false);

const openCreateDialog = () => {
    createTicketDialogVisible.value = true;
};


const fetchTickets = async (page = 1, perPage = pagination.value.per_page) => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get('/api/tickets', {
            params: {
                page,
                per_page: perPage,
            },
        });

        tickets.value = response.data.data;

        pagination.value = {
            current_page: response.data.meta.current_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
        };
    } catch (err) {
        console.error('Failed to load tickets:', err);

        error.value = 'Failed to load tickets.';
    } finally {
        loading.value = false;
    }
};


const handleTicketCreated = () => {
    fetchTickets(
        pagination.value.current_page,
        pagination.value.per_page
    );
};

const viewTicket = (ticket) => {
    selectedTicket.value = ticket;
    viewDialogVisible.value = true;
};

const getStatusLabel = (status) => {
    const item = statuses.find((item) => item.value === status);

    return item?.label ?? status;
};

const getStatusSeverity = (status) => {
    return {
        pending_review: 'warn',
        pending_level_two: 'warn',
        rejected: 'danger',
        sending: 'info',
        send_failed: 'contrast',
        sent: 'success',
    }[status] ?? 'secondary';
};

const onPage = (event) => {
    const page = event.page + 1;

    fetchTickets(page, event.rows);
};


onMounted(() => {
    fetchTickets();
});
</script>

<template>
    <div class="card">
        <div class="font-semibold text-xl mb-4">Tickets</div>
        <DataTable :value="tickets" :paginator="true" dataKey="id" :rowHover="true" lazy
            :rows="pagination.per_page" :totalRecords="pagination.total" :first="(pagination.current_page - 1) * pagination.per_page" @page="onPage" 
            :rowsPerPageOptions="[10, 20, 50]" :loading="loading"
            showGridlines>
            <template #header>
                <div class="flex justify-between">
                    <Button icon="pi pi-plus" label="Add Ticket" @click="openCreateDialog" />
                </div>
            </template>
            <template #empty> No tickets found. </template>
            <template #loading> Loading tickets data. Please wait. </template>

            <Column field="title" header="Title" style="min-width: 250px">
                <template #body="{ data }">
                    <button type="button" class="font-medium text-primary hover:underline cursor-pointer"
                        @click="viewTicket(data)">
                        {{ data.title }}
                    </button>
                </template>
            </Column>

            <Column field="status" header="Status" style="min-width: 150px">
                <template #body="{ data }">
                    <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                </template>
            </Column>

            <Column header="Created" sortField="created_at" style="min-width: 180px">
                <template #body="{ data }">
                    {{ new Date(data.created_at).toLocaleString() }}
                </template>
            </Column>

            <Column header="Actions" style="width: 100px">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" text rounded aria-label="View ticket" @click="viewTicket(data)" />
                </template>
            </Column>
        </DataTable>
    </div>


    <CreateTicketDialog v-model:visible="createTicketDialogVisible" @created="handleTicketCreated" />
    <ViewTicketDialog v-model:visible="viewDialogVisible" :ticket="selectedTicket"  />
</template>
