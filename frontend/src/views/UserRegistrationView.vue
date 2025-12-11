<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <RegistrationForm
          @register="(data, isAdmin = false) => openCaptcha(data, isAdmin)"
          :alert-message="alertMessage"
          :alert-type="alertType"
        />
      </div>
    </div>

    <CaptchaModal
      :visible="captchaVisible"
      @solved="handleCaptchaSolved"
      @cancel="captchaVisible = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import RegistrationForm from '@/components/RegistrationForm.vue'
import CaptchaModal from '@/components/CaptchaModal.vue'

const alertMessage = ref('')
const alertType = ref('')
const router = useRouter()

const captchaVisible = ref(false)
let tempRegData = null
let adminCreation = false

// Called when user clicks "register"
function openCaptcha(userData, isAdmin = false) {
  tempRegData = userData
  adminCreation = isAdmin

  if (isAdmin) {
    // Ak admin, captcha sa preskočí, posielame flag
    handleCaptchaSolved({}, isAdmin)
  } else {
    captchaVisible.value = true
  }
}

// Called when captcha is solved (admin skips)
async function handleCaptchaSolved(captchaData, isAdmin = false) {
  captchaVisible.value = false

  // add is_admin_creation flag if its admin 
  const payload = { ...tempRegData, ...captchaData }
  if (isAdmin) {
    payload.is_admin_creation = true
  }

  alertMessage.value = ''
  alertType.value = ''

  try {
    const response = await fetch('/backend/api/auth-api/user-registration-api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (response.ok && data.success) {
      alertMessage.value = 'Registration successful!'
      alertType.value = 'success'
      setTimeout(() => router.push('/login'), 2000)
    } else {
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
