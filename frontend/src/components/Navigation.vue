<template>
  <nav class="navbar navbar-expand bg-primary navbar-dark">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <span class="me-2" aria-hidden="true">🪙</span>
        <span class="fw-semibold">PFT</span>
      </a>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
            <a class="nav-link" href="#" @click.prevent="handleLogout">Logout</a>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useLoginStore } from '../stores/loginStore'
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const loginStore = useLoginStore()
const router = useRouter()

const isLoggedIn = computed(() => !!loginStore.jwt)
const isAdminUser = computed(() => {
  try {
    const token = loginStore.jwt
    if (!token) return false
    const payload = JSON.parse(atob(token.split('.')[1]))
    return payload.role === 'admin'
  } catch {
    return false
  }
})

const handleLogout = () => {
  const wasAdmin = isAdminUser.value
  loginStore.clearJwt()
  if (wasAdmin) {
    router.push('/admin-login')
  } else {
    router.push('/login')
  }
}
</script>
