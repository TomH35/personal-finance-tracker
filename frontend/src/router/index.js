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
      path: '/login',
      name: 'login',
      component: () => import('../views/UserLoginView.vue'),
    },
  ],
})

export default router
