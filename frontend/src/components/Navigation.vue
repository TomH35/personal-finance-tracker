<template>
  <nav class="navbar navbar-expand-lg shadow-sm py-3" style="background-color: #1D2A5B;">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" class="me-2" viewBox="0 0 16 16">
          <path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1H1zm7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
          <path d="M0 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V7a2 2 0 0 1-2-2H3z"/>
        </svg>
        <span class="fw-bold fs-5 text-white d-none d-sm-inline">Personal Finance Tracker</span>
        <span class="fw-bold fs-6 text-white d-inline d-sm-none">Finance Tracker</span>
      </a>

      <!-- Profile Bubble for Mobile (shown only when logged in on small screens) -->
      <div v-if="isLoggedIn" class="d-lg-none ms-auto me-2">
        <ProfileBubble />
      </div>

      <!-- Hamburger Toggle Button -->
      <button 
        class="navbar-toggler" 
        type="button" 
        @click="toggleNavbar"
        :aria-expanded="isNavbarOpen"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible Navigation -->
      <div class="collapse navbar-collapse" :class="{ show: isNavbarOpen }">
        <ul class="navbar-nav ms-auto mb-0 align-items-lg-center gap-1">
          <li class="nav-item">
            <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/" @click="closeNavbar">
              <span :style="$route.path === '/' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Home</span>
            </RouterLink>
          </li>
          <template v-if="!isLoggedIn">
            <li class="nav-item">
              <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/login" @click="closeNavbar">
                <span :style="$route.path === '/login' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Login</span>
              </RouterLink>
            </li>
          </template>
          <template v-else>
            <li class="nav-item">
              <RouterLink class="nav-link fw-medium px-3 py-2 rounded" style="color: rgba(255, 255, 255, 0.8);" to="/user-dashboard" @click="closeNavbar">
                <span :style="$route.path === '/user-dashboard' ? 'color: white; background-color: rgba(255, 255, 255, 0.15); border-radius: 0.25rem; padding: 0.5rem 0.75rem; display: inline-block;' : ''">Dashboard</span>
              </RouterLink>
            </li>
            <!-- Profile Bubble for Desktop -->
            <li class="nav-item ms-lg-3 d-none d-lg-block">
              <ProfileBubble />
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useLoginStore } from '../stores/loginStore'
import { useRoute } from 'vue-router'
import { computed, ref } from 'vue'
import ProfileBubble from './ProfileBubble.vue'

const loginStore = useLoginStore()
const $route = useRoute()
const isNavbarOpen = ref(false)

const isLoggedIn = computed(() => !!loginStore.jwt)

const toggleNavbar = () => {
  isNavbarOpen.value = !isNavbarOpen.value
}

const closeNavbar = () => {
  isNavbarOpen.value = false
}
</script>

<style scoped>
/* Custom navbar toggler icon color */
.navbar-toggler {
  border-color: rgba(255, 255, 255, 0.3);
}

.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.navbar-toggler:focus {
  box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

/* Ensure mobile menu has proper background */
@media (max-width: 991.98px) {
  .navbar-collapse {
    background-color: #1D2A5B;
    padding: 1rem 0;
  }
}
</style>
