<template>
  <nav class="navbar navbar-expand bg-primary navbar-dark">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <span class="me-2" aria-hidden="true">🪙</span>
        <span class="fw-semibold">PFT</span>
      </a>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item">
          <RouterLink class="nav-link" to="/">Home</RouterLink>
        </li>
        <template v-if="!isLoggedIn">
          <li class="nav-item">
            <RouterLink class="nav-link" to="/login">Login</RouterLink>
          </li>
        </template>
        <template v-else>
          <li class="nav-item">
            <RouterLink class="nav-link" to="/user-dashboard">Dashboard</RouterLink>
          </li>
          <li class="nav-item ms-2">
            <ProfileBubble />
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useLoginStore } from '../stores/loginStore'
import { computed } from 'vue'
import ProfileBubble from './ProfileBubble.vue'

const loginStore = useLoginStore()

const isLoggedIn = computed(() => !!loginStore.jwt)
</script>
