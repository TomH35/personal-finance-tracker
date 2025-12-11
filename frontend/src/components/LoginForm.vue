<template>
  <div class="card border shadow-sm">
    <div class="card-body p-5">
      <h4 class="text-center mb-4 fw-bold" style="color: #1D2A5B;">
        {{ formTitle || 'Login to Your Account' }}
      </h4>

      <form @submit.prevent="handleSubmit">
        <!-- Identifier -->
        <div class="mb-3">
          <label class="form-label fw-medium">Email or Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
              </svg>
            </span>
            <input
              v-model="formData.identifier"
              type="text"
              class="form-control border-start-0"
              placeholder="Enter your email or username"
              required
            />
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label fw-medium">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
              </svg>
            </span>
            <input
              v-model="formData.password"
              type="password"
              class="form-control border-start-0"
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

        <button type="submit" class="btn w-100 fw-semibold" style="background-color: #1D2A5B; color: white; border: none;">Login</button>
      </form>

      <p class="text-center mt-4 mb-0">
        Don't have an account?
        <RouterLink :to="registerLink" class="fw-semibold" style="color: #1D2A5B; text-decoration: none;">Register here</RouterLink>
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
  registerLink: {
    type: String,
    default: '/user-registration', // default for user
  }
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