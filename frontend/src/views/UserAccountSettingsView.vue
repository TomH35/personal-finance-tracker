<template>
  <div class="bg-light min-vh-100">
    <!-- Main Content -->
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-5">
              <h2 class="text-primary mb-2">Account Settings</h2>
              <p class="text-muted mb-5">Update your personal information, change your password, or delete your account.</p>

              <!-- Alert Messages -->
              <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ successMessage }}
                <button type="button" class="btn-close" @click="successMessage = ''"></button>
              </div>
              <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ errorMessage }}
                <button type="button" class="btn-close" @click="errorMessage = ''"></button>
              </div>

              <!-- Profile Information -->
              <div class="mb-5">
                <h5 class="fw-semibold mb-3">Profile Information</h5>
                <form @submit.prevent="updateProfile">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Full Name</label>
                      <input 
                        type="text" 
                        v-model="profileForm.name"
                        class="form-control" 
                        placeholder="John Doe"
                        required
                      />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email Address</label>
                      <input 
                        type="email" 
                        v-model="profileForm.email"
                        class="form-control" 
                        placeholder="john@example.com"
                        required
                      />
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" :disabled="loadingProfile">
                      <span v-if="loadingProfile" class="spinner-border spinner-border-sm me-2"></span>
                      Save Changes
                    </button>
                  </div>
                </form>
              </div>

              <hr class="my-5">

              <!-- Change Password -->
              <div class="mb-5">
                <h5 class="fw-semibold mb-3">Change Password</h5>
                <form @submit.prevent="updatePassword">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label">Current Password</label>
                      <input 
                        type="password" 
                        v-model="passwordForm.currentPassword"
                        class="form-control" 
                        placeholder="••••••••"
                        required
                      />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">New Password</label>
                      <input 
                        type="password" 
                        v-model="passwordForm.newPassword"
                        class="form-control" 
                        placeholder="New password"
                        required
                      />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Confirm Password</label>
                      <input 
                        type="password" 
                        v-model="passwordForm.confirmPassword"
                        class="form-control" 
                        placeholder="Confirm password"
                        required
                      />
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button type="submit" class="btn btn-outline-primary" :disabled="loadingPassword">
                      <span v-if="loadingPassword" class="spinner-border spinner-border-sm me-2"></span>
                      Update Password
                    </button>
                  </div>
                </form>
              </div>

              <hr class="my-5">

              <!-- Danger Zone -->
              <div>
                <h5 class="fw-semibold text-danger mb-2">Danger Zone</h5>
                <p class="text-muted mb-3">Deleting your account is irreversible. All your data and transactions will be permanently removed.</p>
                <button 
                  class="btn btn-danger" 
                  @click="showDeleteModal = true"
                  :disabled="loadingDelete"
                >
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white text-center py-3 mt-auto">
      <div class="container">
        <p class="mb-0">© 2025 Personal Finance Tracker · Privacy · Terms</p>
      </div>
    </footer>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" :class="{ 'show d-block': showDeleteModal }" tabindex="-1" v-if="showDeleteModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title text-danger">Delete Account</h5>
            <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
          </div>
          <div class="modal-body">
            <p>Are you absolutely sure you want to delete your account?</p>
            <p class="text-danger fw-semibold">This action cannot be undone. All your data will be permanently deleted.</p>
            <div class="mb-3">
              <label class="form-label">Enter your password to confirm:</label>
              <input 
                type="password" 
                v-model="deleteForm.password"
                class="form-control" 
                placeholder="Your password"
              />
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
            <button 
              type="button" 
              class="btn btn-danger" 
              @click="deleteAccount"
              :disabled="!deleteForm.password || loadingDelete"
            >
              <span v-if="loadingDelete" class="spinner-border spinner-border-sm me-2"></span>
              Yes, Delete My Account
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showDeleteModal"></div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useLoginStore } from '@/stores/loginStore'
import { useRouter } from 'vue-router'

export default {
  name: 'AccountSettingsView',
  setup() {
    const loginStore = useLoginStore()
    const router = useRouter()

    const successMessage = ref('')
    const errorMessage = ref('')
    const loadingProfile = ref(false)
    const loadingPassword = ref(false)
    const loadingDelete = ref(false)
    const showDeleteModal = ref(false)

    const profileForm = ref({
      name: '',
      email: ''
    })

    const passwordForm = ref({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    })

    const deleteForm = ref({
      password: ''
    })

    // Load user profile
    async function loadProfile() {
      try {
        // TODO: Implement API call to get user profile
        // For now, using placeholder data
        profileForm.value.name = 'John Doe'
        profileForm.value.email = 'john@example.com'
      } catch (err) {
        console.error('Failed to load profile:', err)
      }
    }

    // Update profile
    async function updateProfile() {
      loadingProfile.value = true
      errorMessage.value = ''
      successMessage.value = ''

      try {
        // TODO: Implement API call to update profile
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
        
        successMessage.value = 'Profile updated successfully!'
        setTimeout(() => successMessage.value = '', 3000)
      } catch (err) {
        errorMessage.value = 'Failed to update profile'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loadingProfile.value = false
      }
    }

    // Update password
    async function updatePassword() {
      if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
        errorMessage.value = 'New passwords do not match'
        setTimeout(() => errorMessage.value = '', 3000)
        return
      }

      if (passwordForm.value.newPassword.length < 6) {
        errorMessage.value = 'Password must be at least 6 characters'
        setTimeout(() => errorMessage.value = '', 3000)
        return
      }

      loadingPassword.value = true
      errorMessage.value = ''
      successMessage.value = ''

      try {
        // TODO: Implement API call to update password
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
        
        successMessage.value = 'Password updated successfully!'
        passwordForm.value = {
          currentPassword: '',
          newPassword: '',
          confirmPassword: ''
        }
        setTimeout(() => successMessage.value = '', 3000)
      } catch (err) {
        errorMessage.value = 'Failed to update password'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loadingPassword.value = false
      }
    }

    // Delete account
    async function deleteAccount() {
      if (!deleteForm.value.password) {
        return
      }

      loadingDelete.value = true
      errorMessage.value = ''

      try {
        // TODO: Implement API call to delete account
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
        
        // Logout and redirect
        loginStore.clearJwt()
        router.push('/user-login')
      } catch (err) {
        showDeleteModal.value = false
        errorMessage.value = 'Failed to delete account'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loadingDelete.value = false
      }
    }

    onMounted(async () => {
      loginStore.loadJwt()
      if (!loginStore.jwt) {
        router.push('/user-login')
        return
      }

      await loadProfile()
    })

    return {
      successMessage,
      errorMessage,
      loadingProfile,
      loadingPassword,
      loadingDelete,
      showDeleteModal,
      profileForm,
      passwordForm,
      deleteForm,
      updateProfile,
      updatePassword,
      deleteAccount
    }
  }
}
</script>

<style scoped>
.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
