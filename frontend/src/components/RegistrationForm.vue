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
              :class="{ 'is-invalid': formData.password && !passwordRequirementsMet }"
              placeholder="Enter your password"
              required
            />
          </div>
          <!-- Password Requirements -->
          <div v-if="formData.password" class="small mt-2">
            <div :class="passwordRequirements.length ? 'text-success' : 'text-danger'">
              <i :class="passwordRequirements.length ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
              At least 8 characters
            </div>
            <div :class="passwordRequirements.uppercase ? 'text-success' : 'text-danger'">
              <i :class="passwordRequirements.uppercase ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
              At least one uppercase letter (A-Z)
            </div>
            <div :class="passwordRequirements.lowercase ? 'text-success' : 'text-danger'">
              <i :class="passwordRequirements.lowercase ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
              At least one lowercase letter (a-z)
            </div>
            <div :class="passwordRequirements.number ? 'text-success' : 'text-danger'">
              <i :class="passwordRequirements.number ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
              At least one number (0-9)
            </div>
            <div :class="passwordRequirements.special ? 'text-success' : 'text-danger'">
              <i :class="passwordRequirements.special ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
              At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
            </div>
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
        <RouterLink :to="loginLink" class="fw-semibold text-primary">Login here</RouterLink>
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
  loginLink: {
    type: String,
    default: '/login', // default for user
  }
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

// Password requirements validation
const passwordRequirements = computed(() => {
  const password = formData.value.password
  return {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
  }
})

const passwordRequirementsMet = computed(() => {
  const reqs = passwordRequirements.value
  return reqs.length && reqs.uppercase && reqs.lowercase && reqs.number && reqs.special
})

const handleSubmit = () => {
  if (passwordMismatch.value) {
    return
  }

  if (!passwordRequirementsMet.value) {
    return
  }

  emit('register', {
    username: formData.value.username,
    email: formData.value.email,
    password: formData.value.password,
  })
}
</script>
