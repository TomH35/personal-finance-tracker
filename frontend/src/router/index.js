import { createRouter, createWebHistory } from 'vue-router'
import FrontpageView from '../views/FrontpageView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: FrontpageView,
    },
    {
      path: '/user-registration',
      name: 'user-registration',
      component: () => import('../views/UserRegistrationView.vue'),
    },
    {
      path: '/admin-registration',
      name: 'admin-registration',
      component: () => import('../views/AdminRegistrationView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/UserLoginView.vue'),
    },
    {
      path: '/user-dashboard',
      name: 'user-dashboard',
      component: () => import('../views/UserDashboardView.vue'),
    },
    {
      path: '/admin-login',
      name: 'admin-login',
      component: () => import('../views/AdminLoginView.vue'), 
    },
    {
      path: '/admin-dashboard',      
      name: 'admin-dashboard',
      component: () => import('../views/AdminDashboardView.vue'),
    },
    {
      path: '/account-settings',
      name: 'account-settings',
      component: () => import('../views/AccountSettingsView.vue'),
    },
  ],
})

export default router
