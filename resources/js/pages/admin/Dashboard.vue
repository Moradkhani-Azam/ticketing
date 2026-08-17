<script setup>
import StatsWidget from '@/components/dashboard/StatsWidget.vue';
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const stats = ref({
    total: 0,
    pending_review: 0,
    pending_level_two: 0,
    sending: 0,
    sent: 0,
    send_failed: 0,
    rejected: 0,
});

const loading = ref(false);

const fetchDashboard = async () => {
    loading.value = true;

    try {
        const response = await api.get('/api/admin/dashboard');

        stats.value = response.data.data;
    } catch (error) {
        console.error('Failed to load dashboard data.', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchDashboard);
</script>

<template>
    <div class="grid grid-cols-12 gap-8">

        <StatsWidget
            title="Total Tickets"
            :value="stats.total"
            icon="pi-ticket"
            icon-class="text-blue-500"
            icon-bg-class="bg-blue-100 dark:bg-blue-400/10"
            description="All tickets"
        />

        <StatsWidget
            title="Pending Review"
            :value="stats.pending_review"
            icon="pi-clock"
            icon-class="text-orange-500"
            icon-bg-class="bg-orange-100 dark:bg-orange-400/10"
            description="Waiting for level 1 approval"
        />

        <StatsWidget
            title="Pending Level 2"
            :value="stats.pending_level_two"
            icon="pi-user"
            icon-class="text-purple-500"
            icon-bg-class="bg-purple-100 dark:bg-purple-400/10"
            description="Waiting for level 2 approval"
        />

        <StatsWidget
            title="Sending"
            :value="stats.sending"
            icon="pi-send"
            icon-class="text-cyan-500"
            icon-bg-class="bg-cyan-100 dark:bg-cyan-400/10"
            description="Being sent to external API"
        />

        <StatsWidget
            title="Sent"
            :value="stats.sent"
            icon="pi-check-circle"
            icon-class="text-green-500"
            icon-bg-class="bg-green-100 dark:bg-green-400/10"
            description="Successfully sent"
        />

        <StatsWidget
            title="Send Failed"
            :value="stats.send_failed"
            icon="pi-exclamation-triangle"
            icon-class="text-red-500"
            icon-bg-class="bg-red-100 dark:bg-red-400/10"
            description="Failed to send"
        />

        <StatsWidget
            title="Rejected"
            :value="stats.rejected"
            icon="pi-times-circle"
            icon-class="text-red-500"
            icon-bg-class="bg-red-100 dark:bg-red-400/10"
            description="Rejected tickets"
        />

    </div>
</template>