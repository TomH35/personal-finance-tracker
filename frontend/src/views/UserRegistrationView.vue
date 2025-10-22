<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <RegistrationForm @register="handleRegistration" />
      </div>
    </div>
  </div>
</template>

<script setup>
import RegistrationForm from '@/components/RegistrationForm.vue'

const handleRegistration = (userData) => {
  console.log('Registration data received:', userData)
  // Here you would typically:
  // 1. Call an API to register the user
  // 2. Handle success/error responses
  // 3. Redirect to login or dashboard
  // Example:
  // try {
  //   await api.register(userData)
  //   router.push('/login')
  // } catch (error) {
  //   console.error('Registration failed:', error)
  // }
}
</script>
