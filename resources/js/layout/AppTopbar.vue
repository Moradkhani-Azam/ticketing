<script setup>
import { ref } from 'vue';

import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useLayout } from '@/layout/composables/layout';
import AppConfigurator from './AppConfigurator.vue';
import Icon from '@/components/Icon.vue';

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();
const authStore = useAuthStore();
const router = useRouter();
const profileMenu = ref();

const toggleProfileMenu = (event) => {
    profileMenu.value.toggle(event);
};
const logout = async () => {
    await authStore.logout();
    router.push({
        name: 'login',
    });
};
</script>

<template>
    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
                <i class="pi pi-bars"></i>
            </button>
            <router-link to="/" class="layout-topbar-logo">
                <Icon />

                <span>TICKETING</span>
            </router-link>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
                    <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
                </button>
                <div class="relative">
                    <button
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
                        type="button"
                        class="layout-topbar-action layout-topbar-action-highlight"
                    >
                        <i class="pi pi-palette"></i>
                    </button>
                    <AppConfigurator />
                </div>
            </div>

            <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <button
                        type="button"
                        class="layout-topbar-action"
                        @click="toggleProfileMenu"
                    >
                        <i class="pi pi-user"></i>

                        <span>
                            {{ authStore.user?.name }}
                        </span>

                    </button>

                    <Menu
                        ref="profileMenu"
                        :model="[
                            {
                                label: authStore.user?.name,
                                disabled: true
                            },
                            {
                                separator: true
                            },
                            {
                                label: 'Logout',
                                icon: 'pi pi-sign-out',
                                command: logout
                            }
                        ]"
                        :popup="true"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
