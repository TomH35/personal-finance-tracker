<template>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h5 class="text-center mb-4 fw-semibold text-primary">Create Your Account</h5>
      <form @submit.prevent="handleSubmit">
        <!-- Username -->
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">👤</span>
            <input
              v-model="formData.username"
              type="text"
              class="form-control"
              placeholder="Enter your username"
              required
            />
          </div>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">✉️</span>
            <input
              v-model="formData.email"
              type="email"
              class="form-control"
              placeholder="Enter your email"
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

        <!-- Confirm Password -->
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">🔒</span>
            <input
              v-model="formData.confirmPassword"
              type="password"
              class="form-control"
              placeholder="Confirm your password"
              required
            />
          </div>
          <div v-if="passwordMismatch" class="text-danger small mt-1">Passwords do not match</div>
        </div>

        <!-- Terms -->
        <div class="form-check mb-3">
          <input
            v-model="formData.agreedToTerms"
            class="form-check-input"
            type="checkbox"
            id="termsCheck"
            required
          />
          <label class="form-check-label" for="termsCheck">
            I agree to the <a href="#">Terms and Conditions</a>
          </label>
        </div>
        <!-- Success Alert -->
        <div
          v-if="alertMessage && alertType === 'success'"
          class="alert alert-success"
          role="alert"
        >
          {{ alertMessage }}
        </div>

        <!-- Error Alert -->
        <div v-if="alertMessage && alertType === 'error'" class="alert alert-danger" role="alert">
          {{ alertMessage }}
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>

      <p class="text-center mt-3 mb-0">
        Already have an account?
        <RouterLink to="/login">Login here</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const emit = defineEmits(['register'])

const props = defineProps({
  alertMessage: {
    type: String,
    default: '',
  },
  alertType: {
    type: String,
    default: '',
  },
})

const formData = ref({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  agreedToTerms: false,
})

const passwordMismatch = computed(() => {
  return (
    formData.value.password &&
    formData.value.confirmPassword &&
    formData.value.password !== formData.value.confirmPassword
  )
})

const handleSubmit = () => {
  if (passwordMismatch.value) {
    return
  }

  emit('register', {
    username: formData.value.username,
    email: formData.value.email,
    password: formData.value.password,
  })
}
</script>
