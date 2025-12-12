import { defineStore } from 'pinia'
import { ref } from 'vue'
import router from '@/router'

export const useLoginStore = defineStore('loginStore', () => {
  // State
  const jwt = ref('')
  const refreshToken = ref('')
  const userData = ref(null)
  const isRefreshing = ref(false)
  let refreshPromise = null

  // Save JWT to store and localStorage or sessionStorage
  function setJwt(token, remember = true) {
    jwt.value = token
    if (remember) {
      // Persistent login - use localStorage
      localStorage.setItem('jwt', token)
      sessionStorage.removeItem('jwt') // Clear session storage if exists
    } else {
      // Temporary login - use sessionStorage (cleared when browser closes)
      sessionStorage.setItem('jwt', token)
      localStorage.removeItem('jwt') // Clear localStorage if exists
    }
  }

  // Load JWT from sessionStorage or localStorage
  function loadJwt() {
    // Check sessionStorage first (temporary login)
    let token = sessionStorage.getItem('jwt')
    if (token) {
      jwt.value = token
      return
    }
    
    // Then check localStorage (persistent login)
    token = localStorage.getItem('jwt')
    if (token) {
      jwt.value = token
    }
  }

  // Save refresh token to store and localStorage
  function setRefreshToken(token) {
    refreshToken.value = token
    localStorage.setItem('refreshToken', token)
  }

  // Load refresh token from localStorage
  function loadRefreshToken() {
    const token = localStorage.getItem('refreshToken')
    if (token) {
      refreshToken.value = token
    }
  }

  // Clear all tokens (logout)
  async function clearTokens() {
    // Revoke refresh token on server if it exists
    if (refreshToken.value) {
      try {
        await fetch('/backend/api/auth-api/logout-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ refresh_token: refreshToken.value })
        })
      } catch (error) {
        console.error('Error revoking refresh token:', error)
      }
    }

    jwt.value = ''
    refreshToken.value = ''
    localStorage.removeItem('jwt')
    sessionStorage.removeItem('jwt') // Also clear sessionStorage
    localStorage.removeItem('refreshToken')

    userData.value = null
    localStorage.removeItem('userData')
  }

  // Legacy clearJwt for backwards compatibility
  function clearJwt() {
    clearTokens()
  }

  function setUserData(data) {
    userData.value = data
    localStorage.setItem('userData', JSON.stringify(data))
  }

  function loadUserData() {
    const saved = localStorage.getItem('userData')
    if (saved) userData.value = JSON.parse(saved)
  }

  // Refresh the access token using the refresh token
  async function refreshAccessToken() {
    // If already refreshing, return the existing promise
    if (isRefreshing.value && refreshPromise) {
      return refreshPromise
    }

    if (!refreshToken.value) {
      return { success: false, message: 'No refresh token available' }
    }

    isRefreshing.value = true
    
    refreshPromise = (async () => {
      try {
        const response = await fetch('/backend/api/auth-api/refresh-token-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ refresh_token: refreshToken.value })
        })

        const result = await response.json()

        if (result.success && result.token) {
          setJwt(result.token)
          if (result.user) {
            setUserData(result.user)
          }
          return { success: true, token: result.token }
        } else {
          // Refresh token is invalid/expired, clear everything
          jwt.value = ''
          refreshToken.value = ''
          localStorage.removeItem('jwt')
          sessionStorage.removeItem('jwt') // Also clear sessionStorage
          localStorage.removeItem('refreshToken')
          userData.value = null
          localStorage.removeItem('userData')
          router.push('/login')
          return { success: false, message: result.message || 'Token refresh failed' }
        }
      } catch (error) {
        console.error('Error refreshing token:', error)
        return { success: false, message: 'Network error during token refresh' }
      } finally {
        isRefreshing.value = false
        refreshPromise = null
      }
    })()

    return refreshPromise
  }

  // Check if user is logged in (has valid tokens)
  function isLoggedIn() {
    return !!jwt.value || !!refreshToken.value
  }

  return { 
    jwt, 
    refreshToken,
    isRefreshing,
    setJwt, 
    loadJwt, 
    clearJwt,
    clearTokens,
    setRefreshToken,
    loadRefreshToken,
    refreshAccessToken,
    isLoggedIn,
    userData, 
    setUserData, 
    loadUserData 
  }
})
