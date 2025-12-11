<template>
  <nav class="navbar navbar-expand shadow-sm py-3" style="background-color: #1D2A5B;">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" class="me-2" viewBox="0 0 16 16">
          <path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1H1zm7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
          <path d="M0 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V7a2 2 0 0 1-2-2H3z"/>
        </svg>
        <span class="fw-bold fs-4 text-white">Personal Finance Tracker</span>
      </a>

      <ul class="navbar-nav ms-auto mb-0 align-items-center gap-1">
        <li class="nav-item">
          <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/">
            <span :style="$route.path === '/' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Home</span>
          </RouterLink>
        </li>
        <template v-if="!isLoggedIn">
          <li class="nav-item">
            <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/login">
              <span :style="$route.path === '/login' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Login</span>
            </RouterLink>
          </li>
        </template>
        <template v-else>
          <li class="nav-item">
            <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/user-dashboard">
              <span :style="$route.path === '/user-dashboard' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Dashboard</span>
            </RouterLink>
          </li>
          <li class="nav-item ms-3">
            <ProfileBubble />
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useLoginStore } from '../stores/loginStore'
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import ProfileBubble from './ProfileBubble.vue'

const loginStore = useLoginStore()
const $route = useRoute()

const isLoggedIn = computed(() => !!loginStore.jwt)
</script>
