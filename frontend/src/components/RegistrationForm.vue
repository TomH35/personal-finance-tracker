<template>
  <div class="card border shadow-sm">
    <div class="card-body p-5">
      <h4 class="text-center mb-4 fw-bold" style="color: #1D2A5B;">Create Your Account</h4>
      <form @submit.prevent="handleSubmit">
        <!-- Username -->
        <div class="mb-3">
          <label class="form-label fw-medium">Username</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
              </svg>
            </span>
            <input
              v-model="formData.username"
              type="text"
              class="form-control border-start-0"
              placeholder="Enter your username"
              required
            />
          </div>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
          <label class="form-label fw-medium">Email Address</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
              </svg>
            </span>
            <input
              v-model="formData.email"
              type="email"
              class="form-control border-start-0"
              placeholder="Enter your email"
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
          <label class="form-label fw-medium">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
              </svg>
            </span>
            <input
              v-model="formData.confirmPassword"
              type="password"
              class="form-control border-start-0"
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
        <button type="submit" class="btn w-100 fw-semibold" style="background-color: #1D2A5B; color: white; border: none;">Register</button>
      </form>

      <p class="text-center mt-4 mb-0">
        Already have an account?
        <RouterLink :to="loginLink" class="fw-semibold" style="color: #1D2A5B; text-decoration: none;">Login here</RouterLink>
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
