import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLoginStore = defineStore('loginStore', () => {
  // State
  const jwt = ref('')

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
  }

  return { jwt, setJwt, loadJwt, clearJwt }
})
