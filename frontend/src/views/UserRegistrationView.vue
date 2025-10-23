<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <RegistrationForm
          @register="handleRegistration"
          :alert-message="alertMessage"
          :alert-type="alertType"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import RegistrationForm from '@/components/RegistrationForm.vue'

const alertMessage = ref('')
const alertType = ref('')
const router = useRouter()

const handleRegistration = async (userData) => {
  alertMessage.value = ''
  alertType.value = ''
  try {
    const response = await fetch('/backend/api/user-api/user-registration-api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(userData),
    })

    const data = await response.json()

    if (response.ok) {
      console.log('Registration successful:', data)
      alertMessage.value = 'Registration successful!'
      alertType.value = 'success'
      setTimeout(() => router.push('/login'), 2000)
    } else {
      console.error('Registration failed:', data)
      alertMessage.value = data.message || 'Registration failed. Please try again.'
      alertType.value = 'error'
    }
  } catch (error) {
    console.error('Network error during registration:', error)
    alertMessage.value = 'Network error. Please check your connection and try again.'
    alertType.value = 'error'
  }
}
</script>
