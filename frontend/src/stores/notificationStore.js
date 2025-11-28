import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authenticatedFetch } from '@/utils/api'

export const useNotificationStore = defineStore('notificationStore', () => {
  const notifications = ref([])

  // Load notifications from API
  async function loadNotifications() {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-get-all-api.php')
      const data = await res.json()
      
      if (data.success) {
        // Transform API response to match frontend format
        notifications.value = data.notifications.map(n => ({
          id: n.notification_id,
          type: n.type,
          message: n.message,
          timestamp: n.created_at,
          read: n.is_read === 1 || n.is_read === true
        }))
      }
    } catch (error) {
      console.error('Failed to load notifications:', error)
    }
  }

  // Add a new notification (create in API)
  async function addNotification(type, message, limit_id = null) {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-create-api.php', {
        method: 'POST',
        body: JSON.stringify({
          type,
          message,
          limit_id
        })
      })
      
      const data = await res.json()
      
      if (data.success) {
        // Reload notifications from API to get the new one
        await loadNotifications()
      }
      
      return data
    } catch (error) {
      console.error('Failed to add notification:', error)
      return { success: false, message: 'Network error' }
    }
  }

  // Remove a notification by ID (delete from API)
  async function removeNotification(id) {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-delete-api.php', {
        method: 'DELETE',
        body: JSON.stringify({
          notification_id: id
        })
      })
      
      const data = await res.json()
      
      if (data.success) {
        // Remove from local state
        notifications.value = notifications.value.filter(n => n.id !== id)
      }
    } catch (error) {
      console.error('Failed to remove notification:', error)
    }
  }

  // Mark notification as read (update in API)
  async function markAsRead(id) {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-mark-read-api.php', {
        method: 'PUT',
        body: JSON.stringify({
          notification_id: id
        })
      })
      
      const data = await res.json()
      
      if (data.success) {
        // Update local state
        const notification = notifications.value.find(n => n.id === id)
        if (notification) {
          notification.read = true
        }
      }
    } catch (error) {
      console.error('Failed to mark notification as read:', error)
    }
  }

  // Mark all as read (update in API)
  async function markAllAsRead() {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-mark-all-read-api.php', {
        method: 'PUT'
      })
      
      const data = await res.json()
      
      if (data.success) {
        // Update local state
        notifications.value.forEach(n => n.read = true)
      }
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error)
    }
  }

  // Clear all notifications (delete from API)
  async function clearAll() {
    try {
      const res = await authenticatedFetch('/backend/api/notification-api/notification-delete-all-api.php', {
        method: 'DELETE'
      })
      
      const data = await res.json()
      
      if (data.success) {
        // Clear local state
        notifications.value = []
      }
    } catch (error) {
      console.error('Failed to clear all notifications:', error)
    }
  }

  // Get unread count
  function getUnreadCount() {
    return notifications.value.filter(n => !n.read).length
  }

  // Clear notifications when user logs out
  function clearOnLogout() {
    notifications.value = []
  }

  return {
    notifications,
    loadNotifications,
    addNotification,
    removeNotification,
    markAsRead,
    markAllAsRead,
    clearAll,
    getUnreadCount,
    clearOnLogout
  }
})
