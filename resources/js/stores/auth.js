import { defineStore } from 'pinia';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        roles: [],
        permissions: [],
        loading: false,
        initialized: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,

        hasRole: (state) => (role) => {
            return state.roles.includes(role);
        },

        hasPermission: (state) => (permission) => {
            return state.permissions.includes(permission);
        },

        isAdmin: (state) => !!state.user.is_admin,
    },

    actions: {
        async register(name, email, password, passwordConfirmation) {
            this.loading = true;

            try {
                await api.get('/sanctum/csrf-cookie');

                await api.post('/register', {
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                });

                await this.fetchUser();
            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            this.loading = true;

            try {
                const response = await api.get('/api/me');
                const data = response.data.data;

                this.user = data.user;
                this.roles = data.roles;
                this.permissions = data.permissions;

                return true;
            } catch (error) {
                this.user = null;
                this.roles = [];
                this.permissions = [];

                return false;
            } finally {
                this.loading = false;
                this.initialized = true;
            }
        },

        async login(email, password, remember = false) {
            this.loading = true;

            try {
                await api.get('/sanctum/csrf-cookie');

                await api.post('/login', {
                    email,
                    password,
                    remember,
                });

                await this.fetchUser();

                return true;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post('/logout');
            } finally {
                this.user = null;
                this.roles = [];
                this.permissions = [];
            }
        },

        clear() {
            this.user = null;
            this.roles = [];
            this.permissions = [];
            this.initialized = false;
        },
    },
});