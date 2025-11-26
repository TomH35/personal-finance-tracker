<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <LoginForm
          @login="handleLogin"
          :alert-message="alertMessage"
          :alert-type="alertType"
          form-title="Welcome Back"
          register-link="/user-registration"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import LoginForm from '@/components/LoginForm.vue'
import { useLoginStore } from '@/stores/loginStore'

const alertMessage = ref('')
const alertType = ref('')
const router = useRouter()
const loginStore = useLoginStore() 

const handleLogin = async (loginData) => {
  alertMessage.value = ''
  alertType.value = ''

  try {
    const response = await fetch('/backend/api/auth-api/user-login-api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(loginData),
    })

    const data = await response.json()

    if (response.ok && data.success) {
      console.log('User login successful:', data)
      alertMessage.value = 'Login successful! Redirecting...'
      alertType.value = 'success'

      if (data.token) {
        loginStore.setJwt(data.token) // Save token to store and localStorage
        loginStore.setUserData(data.user)
      }

      setTimeout(() => router.push('/user-dashboard'), 2000)
    } else {
      alertMessage.value = data.message || 'Invalid credentials. Please try again.'
      alertType.value = 'error'
    }
  } catch (error) {
    console.error('Network error during login:', error)
    alertMessage.value = 'Network error. Please check your connection and try again.'
    alertType.value = 'error'
  }
}
</script>
