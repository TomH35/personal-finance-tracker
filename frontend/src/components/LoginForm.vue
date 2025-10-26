<template>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h5 class="text-center mb-4 fw-semibold text-primary">
        {{ formTitle || 'Login to Your Account' }}
      </h5>

      <form @submit.prevent="handleSubmit">
        <!-- Identifier -->
        <div class="mb-3">
          <label class="form-label">Email or Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">👤</span>
            <input
              v-model="formData.identifier"
              type="text"
              class="form-control"
              placeholder="Enter your email or username"
              required
            />
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">🔒</span>
            <input
              v-model="formData.password"
              type="password"
              class="form-control"
              placeholder="Enter your password"
              required
            />
          </div>
        </div>

        <!-- Remember me -->
        <div class="form-check mb-3">
          <input v-model="formData.remember" class="form-check-input" type="checkbox" id="rememberCheck" />
          <label class="form-check-label" for="rememberCheck">
            Remember me
          </label>
        </div>

        <!-- Alerts -->
        <div v-if="alertMessage && alertType === 'success'" class="alert alert-success" role="alert">
          {{ alertMessage }}
        </div>
        <div v-if="alertMessage && alertType === 'error'" class="alert alert-danger" role="alert">
          {{ alertMessage }}
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Login</button>
      </form>

      <p class="text-center mt-3 mb-0 small">
        Don't have an account?
        <RouterLink to="/register" class="fw-semibold text-primary">Register here</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['login'])

const props = defineProps({
  alertMessage: {
    type: String,
    default: '',
  },
  alertType: {
    type: String,
    default: '',
  },
  formTitle: {
    type: String,
    default: '',
  },
})

const formData = ref({
  identifier: '',      
  password: '',
  remember: false,
})

const handleSubmit = () => {
  if (!formData.value.identifier || !formData.value.password) return;

  emit('login', {
    identifier: formData.value.identifier,
    password: formData.value.password,
    remember: formData.value.remember,
  })
}
</script>