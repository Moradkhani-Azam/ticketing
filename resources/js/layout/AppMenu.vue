<script setup>
import { computed } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const model = computed(() => {
    const items = [
        {
            label: 'Home',
            items: [
                {
                    label: 'Dashboard',
                    icon: 'pi pi-fw pi-home',
                    to: '/dashboard'
                },
                {
                    label: 'My Tickets',
                    icon: 'pi pi-fw pi-ticket',
                    to: '/tickets'
                }
            ]
        }
    ];


    if (authStore.isAdmin) {
        items.push({
            label: 'Administration',
            items: [
                {
                    label: 'Dashboard',
                    icon: 'pi pi-home',
                    to: '/admin',
                },
                {
                    label: 'Tickets',
                    icon: 'pi pi-ticket',
                    to: '/admin/tickets',
                    permission: 'ticket.view-all',
                },
            ],
        });
    }
    return items;
});

const visibleModel = computed(() => {
    return model.value
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => {
                if (!item.permission) {
                    return true;
                }

                return authStore.hasPermission(item.permission);
            }),
        }))
        .filter((group) => group.items.length > 0);
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in visibleModel" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i" />
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
