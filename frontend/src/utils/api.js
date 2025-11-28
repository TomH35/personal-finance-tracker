/**
 * API utility with automatic token refresh
 * 
 * This module provides a fetch wrapper that automatically handles JWT expiration
 * by using the refresh token to get a new access token and retry the failed request.
 */

import { useLoginStore } from '@/stores/loginStore'

/**
 * Makes an authenticated API request with automatic token refresh
 * 
 * @param {string} url - The API endpoint URL
 * @param {Object} options - Fetch options (method, body, headers, etc.)
 * @returns {Promise<Response>} - The fetch response
 */
export async function authenticatedFetch(url, options = {}) {
  const loginStore = useLoginStore()
  
  // Check if body is FormData - don't set Content-Type for FormData
  const isFormData = options.body instanceof FormData
  
  // Prepare headers with JWT
  const headers = {
    ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
    ...options.headers
  }
  
  if (loginStore.jwt) {
    headers['Auth'] = `Bearer ${loginStore.jwt}`
  }
  
  // Make the initial request
  let response = await fetch(url, {
    ...options,
    headers
  })
  
  // If we get a 401 (Unauthorized) and have a refresh token, try to refresh
  if (response.status === 401 && loginStore.refreshToken) {
    // Check if the response indicates token expiration
    const responseClone = response.clone()
    let responseData
    try {
      responseData = await responseClone.json()
    } catch {
      responseData = null
    }
    
    // Check for token expiration indicators
    const isTokenExpired = responseData && (
      responseData.message?.toLowerCase().includes('expired') ||
      responseData.message?.toLowerCase().includes('invalid token') ||
      responseData.error?.toLowerCase().includes('expired')
    )
    
    if (isTokenExpired || response.status === 401) {
      // Attempt to refresh the token
      const refreshResult = await loginStore.refreshAccessToken()
      
      if (refreshResult.success) {
        // Retry the original request with the new token
        headers['Auth'] = `Bearer ${loginStore.jwt}`
        
        response = await fetch(url, {
          ...options,
          headers
        })
      } else {
        // Refresh failed - user needs to log in again
        // The store will have already cleared the tokens
        console.warn('Token refresh failed, user needs to re-authenticate')
      }
    }
  }
  
  return response
}

/**
 * Makes an authenticated GET request
 * 
 * @param {string} url - The API endpoint URL
 * @param {Object} options - Additional fetch options
 * @returns {Promise<Response>} - The fetch response
 */
export async function authGet(url, options = {}) {
  return authenticatedFetch(url, {
    ...options,
    method: 'GET'
  })
}

/**
 * Makes an authenticated POST request
 * 
 * @param {string} url - The API endpoint URL
 * @param {Object} data - The request body data
 * @param {Object} options - Additional fetch options
 * @returns {Promise<Response>} - The fetch response
 */
export async function authPost(url, data = {}, options = {}) {
  return authenticatedFetch(url, {
    ...options,
    method: 'POST',
    body: JSON.stringify(data)
  })
}

/**
 * Makes an authenticated PUT request
 * 
 * @param {string} url - The API endpoint URL
 * @param {Object} data - The request body data
 * @param {Object} options - Additional fetch options
 * @returns {Promise<Response>} - The fetch response
 */
export async function authPut(url, data = {}, options = {}) {
  return authenticatedFetch(url, {
    ...options,
    method: 'PUT',
    body: JSON.stringify(data)
  })
}

/**
 * Makes an authenticated DELETE request
 * 
 * @param {string} url - The API endpoint URL
 * @param {Object} data - The request body data (optional)
 * @param {Object} options - Additional fetch options
 * @returns {Promise<Response>} - The fetch response
 */
export async function authDelete(url, data = null, options = {}) {
  const fetchOptions = {
    ...options,
    method: 'DELETE'
  }
  
  if (data) {
    fetchOptions.body = JSON.stringify(data)
  }
  
  return authenticatedFetch(url, fetchOptions)
}

export default {
  authenticatedFetch,
  authGet,
  authPost,
  authPut,
  authDelete
}
