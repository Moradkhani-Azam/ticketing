<script setup>

import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import api from '@/services/api';
import TicketReviewDialog from './TicketReviewDialog.vue';

const router = useRouter();
const auth = useAuthStore();

const tickets = ref([]);
const loading = ref(false);
const error = ref('');

const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
});

const selectedTickets = ref([]);
const bulkApproving = ref(false);

const selectedTicket = ref(null);
const reviewDialogVisible = ref(false);


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

const openTicket = (ticket) => {
    selectedTicket.value = ticket;
    reviewDialogVisible.value = true;
};

const fetchTickets = async (page = 1, perPage = pagination.value.per_page) => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get('/api/admin/tickets', {
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

        selectedTickets.value = [];
    } catch (err) {
        console.error('Failed to load tickets:', err);

        error.value = 'Failed to load tickets.';
    } finally {
        loading.value = false;
    }
};


const handleTicketUpdated = () => {
    fetchTickets(
        pagination.value.current_page,
        pagination.value.per_page
    );
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

const approveSelected = async () => {

    if (!selectedTickets.value.length) {
        return;
    }

    bulkApproving.value = true;

    try {
        await api.post('/api/admin/tickets/bulk-approve', {
            ticket_ids: selectedTickets.value.map(ticket => ticket.id),
        });

        selectedTickets.value = [];

        await fetchTickets(
            pagination.value.current_page,
            pagination.value.per_page
        );
    } catch (err) {
        console.error('Failed to approve selected tickets:', err);
    } finally {
        bulkApproving.value = false;
    }
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
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 p-3 rounded-md border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-900">
            <div class="flex items-center gap-2">
                <span class="font-medium text-sm">Selected tickets</span>
                <Badge :value="selectedTickets && selectedTickets.length > 0 ? selectedTickets.length.toString() : ''" :severity="selectedTickets && selectedTickets.length ? 'info' : 'secondary'" />
            </div>
            <Button label="Approve all" icon="pi pi-check" severity="success" :disabled="!selectedTickets || !selectedTickets.length" :loading="bulkApproving" @click="approveSelected" />
        </div>
        <DataTable :value="tickets" :paginator="true" dataKey="id" :rowHover="true" lazy
            :rows="pagination.per_page" :totalRecords="pagination.total" :first="(pagination.current_page - 1) * pagination.per_page" @page="onPage" :rowsPerPageOptions="[10, 20, 50]" v-model:selection="selectedTickets" 
            :loading="loading" showGridlines>
            <template #empty> No tickets found. </template>
            <template #loading> Loading tickets data. Please wait. </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem">
                <template #body="{ data }">
                    <Checkbox 
                        :value="data"
                        v-model="selectedTickets"
                        :disabled="!data.can_approve"
                        :binary="false"
                    />
                </template>
            </Column>

            <Column field="id" header="ID" style="width: 100px">
                <template #body="{ data }">
                    #{{ data.id }}
                </template>
            </Column>

            <Column field="title" header="Title" style="min-width: 250px">
                <template #body="{ data }">
                    <button type="button" class="font-medium text-primary hover:underline cursor-pointer"
                        @click="viewTicket(data)">
                        {{ data.title }}
                    </button>
                </template>

            </Column>

            <Column field="user" header="User" style="min-width: 150px">
                <template #body="{ data }">
                    {{ data.user?.name }}
                    <Tag :value="data.user?.id" :severity="'info'" />
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

                    <Button icon="pi pi-eye" text rounded aria-label="View ticket" @click="openTicket(data)" />
                </template>
            </Column>
        </DataTable>
    </div>


    <TicketReviewDialog v-model:visible="reviewDialogVisible" :ticket="selectedTicket" @approved="handleTicketUpdated"
        @rejected="handleTicketUpdated" />
</template>
