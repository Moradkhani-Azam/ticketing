<script setup>
import Icon from '@/components/Icon.vue';
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();

const auth = useAuthStore();

const email = ref('');
const password = ref('');
const checked = ref(false);

import api from '@/services/api';

const loading = ref(false);
const error = ref('');

const login = async () => {
    loading.value = true;
    error.value = '';

    try {
        await auth.login(
            email.value,
            password.value,
            checked.value
        );
        
        const redirect = route.query.redirect;

        if (redirect) {
            await router.push(redirect);
        } else {
            await router.push(
                auth.isAdmin
                    ? { name: 'admin.dashboard' }
                    : { name: 'dashboard' }
            );
        }

    } catch (err) {
        if (err.response?.status === 422) {
            error.value = 'Invalid email or password.';
        } else {
            error.value = 'An error occurred while signing in.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <FloatingConfigurator />
    <div
        class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div
                style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        <Icon class="mb-8 w-16 shrink-0 mx-auto"/>
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Welcome to
                            Ticketing!</div>
                        <span class="text-muted-color font-medium">Sign in to continue</span>
                    </div>

                    <div>
                        <label for="email"
                            class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">Email</label>
                        <InputText id="email" type="text" placeholder="Email address" class="w-full md:w-[30rem] mb-8"
                            v-model="email" />

                        <label for="password"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2">Password</label>
                        <Password id="password" v-model="password" placeholder="Password" :toggleMask="true"
                            class="mb-4" fluid :feedback="false"></Password>

                        <div class="flex items-center justify-between mt-2 mb-8 gap-8">
                            <div class="flex items-center">
                                <Checkbox v-model="checked" id="rememberme1" binary class="mr-2"></Checkbox>
                                <label for="rememberme1">Remember me</label>
                            </div>
                        </div>
                        <Message v-if="error" severity="error" class="mb-4">
                            {{ error }}
                        </Message>
                        <Button label="Sign In" class="w-full" :loading="loading" @click="login"></Button>

                        <div class="text-center mt-6">
                            <span class="text-muted-color">Don't have an account?</span>

                            <router-link
                                :to="{ name: 'register' }"
                                class="font-medium text-primary ml-2 no-underline"
                            >
                                Create an account
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
