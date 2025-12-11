<template>
  <div class="profile-bubble-container" ref="bubbleContainer">
    <!-- Profile Picture Button -->
    <button 
      class="profile-bubble-btn" 
      @click="toggleDropdown"
      :aria-expanded="isOpen"
      aria-label="User profile menu"
    >
      <div class="profile-avatar">
        <img 
          v-if="profilePicture" 
          :src="profilePicture" 
          alt="Profile"
          class="profile-image"
        />
        <div v-else class="profile-initials">
          {{ userInitials }}
        </div>
      </div>
      <!-- Notification Badge -->
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
    </button>

    <!-- Dropdown Menu -->
    <transition name="dropdown">
      <div v-if="isOpen" class="profile-dropdown">
        <!-- User Info Section -->
        <div class="dropdown-header">
          <div class="user-avatar-large">
            <img 
              v-if="profilePicture" 
              :src="profilePicture" 
              alt="Profile"
              class="profile-image-large"
            />
            <div v-else class="profile-initials-large">
              {{ userInitials }}
            </div>
          </div>
          <div class="user-info">
            <div class="user-name">{{ userName }}</div>
            <div class="user-email">{{ userEmail }}</div>
          </div>
        </div>

        <div class="dropdown-divider"></div>

        <!-- Notifications Section -->
        <div v-if="notifications.length > 0" class="notifications-section">
          <div class="notifications-header">
            <span class="notifications-title">Notifications</span>
            <button 
              v-if="notifications.length > 0" 
              @click="clearAllNotifications" 
              class="clear-all-btn"
            >
              Clear all
            </button>
          </div>
          <div class="notifications-list">
            <div 
              v-for="notification in notifications" 
              :key="notification.id"
              :class="['notification-item', notification.type, { 'unread': !notification.read }]"
              @click="markNotificationAsRead(notification.id)"
            >
              <div class="notification-content">
                <div class="notification-icon">
                  {{ notification.type === 'critical' ? '🚨' : '⚠️' }}
                </div>
                <div class="notification-text">
                  <div class="notification-title">{{ getNotificationTitle(notification.message) }}</div>
                  <div class="notification-message">{{ getNotificationBody(notification.message) }}</div>
                  <div class="notification-time">{{ formatTimestamp(notification.timestamp) }}</div>
                </div>
              </div>
              <button 
                @click.stop="deleteNotification(notification.id)" 
                class="notification-delete"
                aria-label="Delete notification"
              >
                ×
              </button>
            </div>
          </div>
          <div class="dropdown-divider"></div>
        </div>

        <!-- Menu Items -->
        <div class="dropdown-menu-items">
          <RouterLink 
            to="/account-settings" 
            class="dropdown-item"
            @click="closeDropdown"
          >
            <span class="dropdown-icon">⚙️</span>
            <span>Settings</span>
          </RouterLink>
          
          <button 
            class="dropdown-item logout-item" 
            @click="handleLogout"
          >
            <span class="dropdown-icon">🚪</span>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLoginStore } from '../stores/loginStore'
import { useNotificationStore } from '../stores/notificationStore'
import { authenticatedFetch } from '@/utils/api'

const router = useRouter()
const loginStore = useLoginStore()
const notificationStore = useNotificationStore()
const bubbleContainer = ref(null)
const isOpen = ref(false)
const profilePicture = ref(null)
const userProfile = ref(null)

const notifications = computed(() => notificationStore.notifications)
const unreadCount = computed(() => notificationStore.getUnreadCount())

const userName = computed(() => {
  return userProfile.value?.username || 'User'
})

const userEmail = computed(() => {
  return userProfile.value?.email || ''
})

const userInitials = computed(() => {
  if (userProfile.value?.username) {
    const username = userProfile.value.username
    // Get first two characters of username, or first character if only one word
    return username.slice(0, 2).toUpperCase()
  }
  return 'U'
})

const isAdminUser = computed(() => {
  try {
    const token = loginStore.jwt
    if (!token) return false
    const payload = JSON.parse(atob(token.split('.')[1]))
    return payload.role === 'admin'
  } catch {
    return false
  }
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

const handleLogout = async () => {
  const wasAdmin = isAdminUser.value
  await loginStore.clearTokens()
  notificationStore.clearOnLogout()
  closeDropdown()
  if (wasAdmin) {
    router.push('/admin-login')
  } else {
    router.push('/login')
  }
}

const handleClickOutside = (event) => {
  if (bubbleContainer.value && !bubbleContainer.value.contains(event.target)) {
    closeDropdown()
  }
}

const formatTimestamp = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getNotificationTitle = (message) => {
  // Extract month/year from beginning of message (e.g., "December 2025")
  const match = message.match(/^([A-Za-z]+\s+\d{4})/)
  return match ? match[1] : ''
}

const getNotificationBody = (message) => {
  // Remove month/year prefix and newline
  return message.replace(/^[A-Za-z]+\s+\d{4}\n/, '')
}

const deleteNotification = (id) => {
  notificationStore.removeNotification(id)
}

const markNotificationAsRead = (id) => {
  notificationStore.markAsRead(id)
}

const clearAllNotifications = () => {
  notificationStore.clearAll()
}

const fetchUserProfile = async () => {
  try {
    const res = await authenticatedFetch('/backend/api/user-api/user-get-profile-api.php')
    const data = await res.json()
    if (data.success) {
      userProfile.value = data.user
    }
  } catch (error) {
    console.error('Error fetching user profile:', error)
  }
}

const fetchProfilePicture = async () => {
  try {
    const res = await authenticatedFetch('/backend/api/user-api/user-get-profile-picture-api.php')
    const data = await res.json()
    if (data.success && data.exists && data.url) {
      // Add timestamp to prevent caching
      profilePicture.value = '/backend' + data.url + '?t=' + Date.now()
    } else {
      profilePicture.value = null
    }
  } catch (error) {
    console.error('Error fetching profile picture:', error)
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  notificationStore.loadNotifications()
  fetchUserProfile()
  fetchProfilePicture()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.profile-bubble-container {
  position: relative;
  display: inline-block;
}

.profile-bubble-btn {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
  position: relative;
}

.profile-bubble-btn:hover {
  opacity: 0.9;
}

.profile-bubble-btn:focus {
  outline: none;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #d93025;
  color: white;
  font-size: 10px;
  font-weight: 600;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: transform 0.2s ease;
}

.profile-bubble-btn:hover .profile-avatar {
  transform: scale(1.05);
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-initials {
  color: white;
  font-weight: 600;
  font-size: 14px;
  user-select: none;
}

/* Dropdown */
.profile-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15), 
              0 2px 8px rgba(0, 0, 0, 0.1);
  min-width: 280px;
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar-large {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.profile-image-large {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-initials-large {
  color: white;
  font-weight: 600;
  font-size: 24px;
  user-select: none;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 600;
  font-size: 16px;
  color: #202124;
  margin-bottom: 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-email {
  font-size: 14px;
  color: #5f6368;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-divider {
  height: 1px;
  background: #e8eaed;
  margin: 0;
}

.dropdown-menu-items {
  padding: 8px 0;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: none;
  color: #202124;
  font-size: 14px;
  transition: background-color 0.15s ease;
  text-align: left;
}

.dropdown-item:hover {
  background-color: #f1f3f4;
}

.dropdown-icon {
  font-size: 18px;
  width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logout-item {
  color: #d93025;
}

/* Notifications Section */
.notifications-section {
  max-height: 400px;
  overflow-y: auto;
}

.notifications-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8f9fa;
}

.notifications-title {
  font-weight: 600;
  font-size: 14px;
  color: #202124;
}

.clear-all-btn {
  background: none;
  border: none;
  color: #1a73e8;
  font-size: 13px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.15s ease;
}

.clear-all-btn:hover {
  background-color: rgba(26, 115, 232, 0.1);
}

.notifications-list {
  padding: 0;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 12px 20px;
  cursor: pointer;
  transition: background-color 0.15s ease;
  border-left: 3px solid transparent;
}

.notification-item.unread {
  background-color: #f1f3f4;
}

.notification-item.warning {
  border-left-color: #f9ab00;
}

.notification-item.critical {
  border-left-color: #d93025;
}

.notification-item:hover {
  background-color: #e8eaed;
}

.notification-content {
  display: flex;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.notification-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.notification-text {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 13px;
  font-weight: 600;
  color: #202124;
  margin-bottom: 2px;
}

.notification-message {
  font-size: 13px;
  color: #5f6368;
  margin-bottom: 4px;
  word-wrap: break-word;
}

.notification-time {
  font-size: 12px;
  color: #5f6368;
}

.notification-delete {
  background: none;
  border: none;
  color: #5f6368;
  font-size: 24px;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  flex-shrink: 0;
  margin-left: 8px;
  line-height: 1;
}

.notification-delete:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: #202124;
}

/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: top right;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-8px);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: scale(1) translateY(0);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-dropdown {
    min-width: 260px;
  }
}
</style>
