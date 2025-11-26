import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLoginStore = defineStore('loginStore', () => {
  // State
  const jwt = ref('')
  const userData = ref(null)

  // Save JWT to store and localStorage
  function setJwt(token) {
    jwt.value = token
    localStorage.setItem('jwt', token)
  }

  // Load JWT from localStorage
  function loadJwt() {
    const token = localStorage.getItem('jwt')
    if (token) {
      jwt.value = token
    }
  }

  // Clear JWT (logout)
  function clearJwt() {
    jwt.value = ''
    localStorage.removeItem('jwt')

    userData.value = null
    localStorage.removeItem('userData')
  }

  function setUserData(data) {
    userData.value = data
    localStorage.setItem('userData', JSON.stringify(data))
  }

  function loadUserData() {
    const saved = localStorage.getItem('userData')
    if (saved) userData.value = JSON.parse(saved)
  }

  return { jwt, setJwt, loadJwt, clearJwt, userData, setUserData, loadUserData }
})
