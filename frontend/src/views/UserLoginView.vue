<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <LoginForm
          @login="handleLoginClick"
          :alert-message="alertMessage"
          :alert-type="alertType"
          form-title="Welcome Back"
          register-link="/user-registration"
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
import LoginForm from '@/components/LoginForm.vue'
import CaptchaModal from '@/components/CaptchaModal.vue'
import { useLoginStore } from '@/stores/loginStore'

const alertMessage = ref('')
const alertType = ref('')
const router = useRouter()
const loginStore = useLoginStore()

const captchaVisible = ref(false)
let tempLoginData = null
let captchaPassed = ref(false)
const waitTimer = ref(0)
let interval = null

// Click on login
async function handleLoginClick(data) {
  tempLoginData = data

  // If captcha was already solved → send login request
  if (captchaPassed.value && waitTimer.value === 0) {
    await sendLoginRequest({ ...tempLoginData })
    captchaPassed.value = false
    return
  }

  // If countdown is running → ignore click and reset timer
  if (waitTimer.value > 0) {
    startWaitTimer(60) // reset to 60s
    return
  }
  // First attempt or captcha_required
  const response = await fetch('/backend/api/auth-api/user-login-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  })
  const resData = await response.json()

  if (resData.success) {
    loginStore.setJwt(resData.token, data.remember)
    if (resData.refresh_token) {
      loginStore.setRefreshToken(resData.refresh_token)
    }
    loginStore.setUserData(resData.user)
    router.push('/user-dashboard')
  } else {
    alertMessage.value = resData.message
    alertType.value = 'error'
    if (resData.captcha_required) {
      captchaVisible.value = true
    }
  }
}

// After captcha is solved
function handleCaptchaSolved(captchaData) {
  captchaVisible.value = false
  captchaPassed.value = true
  tempLoginData = { ...tempLoginData, ...captchaData }

  startWaitTimer(60) // 60s countdown

  alertMessage.value = `Captcha solved! Please wait ${waitTimer.value}s and click 'Login' again to continue.`
  alertType.value = 'success'
}

// Countdown
function startWaitTimer(seconds) {
  waitTimer.value = seconds
  if (interval) clearInterval(interval)

  interval = setInterval(() => {
    waitTimer.value--
    if (waitTimer.value <= 0) {
      clearInterval(interval)
      interval = null
      alertMessage.value = "You can now click 'Login' to continue."
    } else {
      alertMessage.value = `Captcha solved! Please wait ${waitTimer.value}s and click 'Login' again to continue.`
    }
  }, 1000)
}

// Actual login request
async function sendLoginRequest(payload) {
  const response = await fetch('/backend/api/auth-api/user-login-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })
  const data = await response.json()

  if (data.success) {
    loginStore.setJwt(data.token)
    if (data.refresh_token) {
      loginStore.setJwt(data.token, tempLoginData.remember)
    }
    router.push('/user-dashboard')
  } else {
    alertMessage.value = data.message
    alertType.value = 'error'
    if (data.captcha_required) {
      captchaVisible.value = true
      captchaPassed.value = false
      waitTimer.value = 0
    }
  }
}
</script>
