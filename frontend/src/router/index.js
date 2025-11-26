import { createRouter, createWebHistory } from 'vue-router'
import FrontpageView from '../views/FrontpageView.vue'
import { useLoginStore } from '@/stores/loginStore';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: FrontpageView,
      meta: { public: true },
    },
    {
      path: '/user-registration',
      name: 'user-registration',
      component: () => import('../views/UserRegistrationView.vue'),
      meta: { public: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/UserLoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/user-dashboard',
      name: 'user-dashboard',
      component: () => import('../views/UserDashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/admin-login',
      name: 'admin-login',
      component: () => import('../views/AdminLoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/admin-dashboard',      
      name: 'admin-dashboard',
      component: () => import('../views/AdminDashboardView.vue'),
      meta: { requiresAdminAuth: true },
    },
    {
      path: '/account-settings',
      name: 'account-settings',
      component: () => import('../views/UserAccountSettingsView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const loginStore = useLoginStore();

  const isLoggedIn = !!loginStore.jwt;
  const role = loginStore.userData?.role;

  // 1. Public pages – no auth required
  if (to.meta.public) {
    return next();
  }

  // 2. Pages requiring any login
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: 'login' });
  }

  // 3. Pages requiring USER role
  if (to.meta.requiresUserAuth) {
    if (!isLoggedIn || role !== 'user') {
      return next({ name: 'login' });
    }
  }

  // 4. Pages requiring ADMIN role
  if (to.meta.requiresAdminAuth) {
    if (!isLoggedIn || role !== 'admin') {
      return next({ name: 'admin-login' });
    }
  }

  return next();
});

export default router
