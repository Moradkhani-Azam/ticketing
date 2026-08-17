import AppLayout from '@/layout/AppLayout.vue';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: AppLayout,
            meta: {
                requiresAuth: true,
            },
            children: [
                {
                    path: 'dashboard',
                    name: 'dashboard',
                    component: () => import('@/pages/user/Dashboard.vue')
                },
                {
                    path: 'tickets',
                    name: 'tickets',
                    component: () => import('@/pages/user/tickets/Index.vue')
                },
            ],
        },
        {
            path: '/admin',
            component: AppLayout,
            meta: {
                requiresAuth: true
            },
            children: [
                {
                    path: '',
                    name: 'admin.dashboard',
                    component: () => import('@/pages/admin/Dashboard.vue'),
                },
                {
                    path: 'tickets',
                    name: 'admin.tickets',
                    component: () => import('@/pages/admin/tickets/Index.vue'),
                    meta: {
                        permission: 'ticket.view-all',
                    },
                },
            ],
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/pages/NotFound.vue')
        },

        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/pages/auth/Login.vue'),
            meta: {
                guest: true,
            }
        },

        {
            path: '/auth/register',
            name: 'register',
            component: () => import('@/pages/auth/Register.vue'),
            meta: {
                guest: true,
            }
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/pages/auth/Access.vue')
        },
        {
            path: '/error',
            name: 'error',
            component: () => import('@/pages/Error.vue')
        }
    ]
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.initialized) {
        try {
            await authStore.fetchUser();
        } catch {
            return {
                name: 'login',
                query: {
                    redirect: to.fullPath,
                },
            };
        }
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return {
            name: 'login',
            query: {
                redirect: to.fullPath,
            },
        };
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return {
            name: 'dashboard',
        };
    }

    if (to.meta.permission) {
        if (!authStore.isAuthenticated) {
            return {
                name: 'login',
                query: {
                    redirect: to.fullPath,
                },
            };
        }

        if (!authStore.hasPermission(to.meta.permission)) {
            return {
                name: 'accessDenied',
            };
        }
    }

    return true;
});


export default router;
