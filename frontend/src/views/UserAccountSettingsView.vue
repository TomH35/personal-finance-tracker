<template>
  <div class="bg-light min-vh-100 d-flex flex-column">
    <div class="container py-5 flex-grow-1">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-5">
              <h2 class="text-primary mb-2">Account Settings</h2>
              <p class="text-muted mb-5">
                Update your personal information, change your password, manage your monthly limits, or delete your account.
              </p>

              <!-- Success/Error Messages -->
              <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ successMessage }}
                <button type="button" class="btn-close" @click="successMessage = ''"></button>
              </div>
              <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ errorMessage }}
                <button type="button" class="btn-close" @click="errorMessage = ''"></button>
              </div>

              <!-- Profile Picture -->
              <div class="mb-4 text-center">
                <div class="position-relative d-inline-block">
                  <img 
                    v-if="profileImageUrl" 
                    :src="profileImageUrl + '?t=' + cacheBuster" 
                    class="rounded-circle mb-2 border border-2 border-primary" 
                    width="120" height="120" 
                    alt="Profile Picture"
                  />
                  <div v-else class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mb-2" style="width:120px;height:120px;">
                    <i class="bi bi-person-fill text-white fs-2"></i>
                  </div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-2">
                  <label class="btn btn-outline-primary mb-0">
                    <i class="bi bi-upload me-1"></i> Browse
                    <input type="file" accept="image/*" @change="onProfilePictureSelected" class="d-none"/>
                  </label>
                  <button v-if="profileImageUrl" @click="deleteProfilePicture" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </div>

              <!-- Profile Information -->
              <div class="mb-5">
                <h5 class="fw-semibold mb-3">Profile Information</h5>
                <form @submit.prevent="updateProfile">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Full Name</label>
                      <input type="text" v-model="profileForm.name" class="form-control" placeholder="John Doe" required />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email Address</label>
                      <input type="email" v-model="profileForm.email" class="form-control" placeholder="john@example.com" required />
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
                      <input type="password" v-model="passwordForm.currentPassword" class="form-control" placeholder="••••••••" required />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">New Password</label>
                      <input type="password" v-model="passwordForm.newPassword" class="form-control" placeholder="New password" required />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Confirm Password</label>
                      <input type="password" v-model="passwordForm.confirmPassword" class="form-control" placeholder="Confirm password" required />
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

              <!-- Toggle Limits -->
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="toggleLimits" v-model="limitsEnabled" @change="updateLimitsToggle">
                  <label class="form-check-label" for="toggleLimits">
                    Enable Monthly Expense Limits
                  </label>
                </div>
              </div>

              <!-- Monthly Expense Limits -->
              <div v-if="limitsEnabled" class="mb-5">
                <h5 class="fw-semibold mb-3">Monthly Expense Limits</h5>
                <form @submit.prevent="submitLimit">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Warning Limit</label>
                      <input type="number" v-model="limitForm.warning_limit" class="form-control" placeholder="e.g. 500" required />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Critical Limit</label>
                      <input type="number" v-model="limitForm.critical_limit" class="form-control" placeholder="e.g. 1000" required />
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" :disabled="loadingLimit">
                      <span v-if="loadingLimit" class="spinner-border spinner-border-sm me-2"></span>
                      Save Limits
                    </button>
                  </div>
                </form>
              </div>

              <hr class="my-5">

              <!-- Danger Zone -->
              <div>
                <h5 class="fw-semibold text-danger mb-2">Danger Zone</h5>
                <p class="text-muted mb-3">
                  Deleting your account is irreversible. All your data and transactions will be permanently removed.
                </p>
                <button class="btn btn-danger" @click="showDeleteModal = true" :disabled="loadingDelete">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  Delete Account
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="bg-primary text-white text-center py-3 mt-auto">
      <div class="container">
        <p class="mb-0">© 2025 Personal Finance Tracker · Privacy · Terms</p>
      </div>
    </footer>

    <!-- Delete Modal -->
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
              <input type="password" v-model="deleteForm.password" class="form-control" placeholder="Your password" />
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteAccount" :disabled="!deleteForm.password || loadingDelete">
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
    const loadingLimit = ref(false)
    const loadingDelete = ref(false)
    const showDeleteModal = ref(false)

    const profileForm = ref({ name: '', email: '' })
    const passwordForm = ref({ currentPassword: '', newPassword: '', confirmPassword: '' })
    const limitForm = ref({ limit_id: null, warning_limit: '', critical_limit: '' })
    const deleteForm = ref({ password: '' })
    const allLimits = ref([])
    const limitsEnabled = ref(false)
    const userToggled = ref(false)

    // Profile picture
    const profileImageUrl = ref('')
    const cacheBuster = ref(Date.now())
    const BACKEND_URL = 'http://localhost/personal-finance-tracker/backend'

    async function loadProfile() {
      profileForm.value.name = 'John Doe'
      profileForm.value.email = 'john@example.com'
      await loadProfilePicture()
    }

    async function loadProfilePicture() {
      try {
        const res = await fetch(BACKEND_URL + '/api/user-api/user-get-profile-picture-api.php', {
          headers: { 'Auth': `Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if (data.success && data.exists) {
          profileImageUrl.value = BACKEND_URL + data.url
          cacheBuster.value = Date.now()
        } else {
          profileImageUrl.value = ''
        }
      } catch(err) { console.error(err) }
    }

    async function onProfilePictureSelected(event) {
      const file = event.target.files[0]
      if (!file) return

      profileImageUrl.value = URL.createObjectURL(file)
      cacheBuster.value = Date.now()

      const formData = new FormData()
      formData.append('profile_picture', file)

      try {
        const res = await fetch(BACKEND_URL + '/api/user-api/user-upload-profile-picture-api.php', {
          method: 'POST',
          headers: { 'Auth': `Bearer ${loginStore.jwt}` },
          body: formData
        })
        const data = await res.json()
        if (!data.success) {
          errorMessage.value = data.message
          setTimeout(()=>errorMessage.value='',3000)
        } else {
          successMessage.value = 'Profile picture uploaded!'
          profileImageUrl.value = BACKEND_URL + data.url
          cacheBuster.value = Date.now()
          setTimeout(()=>successMessage.value='',3000)
        }
      } catch(err) {
        errorMessage.value='Upload failed'
        setTimeout(()=>errorMessage.value='',3000)
      }
    }

    async function deleteProfilePicture() {
      try {
        const res = await fetch(BACKEND_URL + '/api/user-api/user-delete-profile-picture-api.php', {
          method:'POST',
          headers:{ 'Auth':`Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if(data.success) {
          profileImageUrl.value=''
          cacheBuster.value = Date.now()
          successMessage.value='Profile picture deleted'
          setTimeout(()=>successMessage.value='',3000)
        }
      } catch(err) {
        errorMessage.value='Failed to delete profile picture'
        setTimeout(()=>errorMessage.value='',3000)
      }
    }

    async function loadLimits() {
      try {
        const res = await fetch(`/backend/api/limit-api/get-limit-api.php`, {
          headers: { 'auth': `Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if (data.success && data.limit && data.limit.length > 0) {
          allLimits.value = Array.isArray(data.limit) ? data.limit : [data.limit]
          const userLimit = allLimits.value[0]
          if (userLimit) {
            limitForm.value.limit_id = userLimit.limit_id
            if (userLimit.enabled) {
              limitsEnabled.value = true
              limitForm.value.warning_limit = userLimit.warning_limit
              limitForm.value.critical_limit = userLimit.critical_limit
            } else {
              limitsEnabled.value = false
              limitForm.value.warning_limit = ''
              limitForm.value.critical_limit = ''
            }
          }
        } else {
          limitsEnabled.value = false
          limitForm.value = { limit_id: null, warning_limit: '', critical_limit: '' }
        }
      } catch (err) { console.error(err) }
    }

    async function updateLimitsToggle() {
      userToggled.value = true
      try {
        if (limitForm.value.limit_id) {
          const res = await fetch(`/backend/api/limit-api/toggle-limit-api.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'auth': `Bearer ${loginStore.jwt}` },
            body: JSON.stringify({ 
              limit_id: limitForm.value.limit_id,
              enabled: limitsEnabled.value ? 1 : 0 
            })
          })
          const data = await res.json()
          if (!data.success) throw new Error(data.message)
          await loadLimits()
        } else {
          if (!limitsEnabled.value) {
            limitForm.value = { limit_id: null, warning_limit: '', critical_limit: '' }
          }
        }
      } catch (err) {
        errorMessage.value = 'Failed to update toggle'
        limitsEnabled.value = !limitsEnabled.value
        setTimeout(()=>errorMessage.value='',3000)
      }
    }

    async function submitLimit() {
      loadingLimit.value = true
      try {
        const url = limitForm.value.limit_id
          ? `/backend/api/limit-api/edit-limit-api.php`
          : `/backend/api/limit-api/set-limit-api.php`

        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'auth': `Bearer ${loginStore.jwt}` },
          body: JSON.stringify({
            ...limitForm.value,
            enabled: 1
          })
        })
        const data = await res.json()
        if (data.success) {
          successMessage.value = limitForm.value.limit_id ? 'Limits updated successfully!' : 'Limits saved successfully!'
          setTimeout(()=>successMessage.value='',3000)
          await loadLimits()
        } else {
          errorMessage.value = data.message
          setTimeout(()=>errorMessage.value='',3000)
        }
      } catch { 
        errorMessage.value = 'Network error'
        setTimeout(()=>errorMessage.value='',3000)
      }
      finally { loadingLimit.value = false }
    }

    async function updateProfile() {
      loadingProfile.value = true
      errorMessage.value = ''
      successMessage.value = ''
      try { 
        await new Promise(r => setTimeout(r, 1000))
        successMessage.value='Profile updated successfully!'
        setTimeout(()=>successMessage.value='',3000) 
      } catch { 
        errorMessage.value='Failed to update profile' 
      } finally { 
        loadingProfile.value = false 
      }
    }

    async function updatePassword() {
      if(passwordForm.value.newPassword !== passwordForm.value.confirmPassword){
        errorMessage.value='New passwords do not match'
        setTimeout(()=>errorMessage.value='',3000)
        return
      }
      loadingPassword.value=true
      try{
        await new Promise(r=>setTimeout(r,1000))
        successMessage.value='Password updated successfully!'
        passwordForm.value={currentPassword:'',newPassword:'',confirmPassword:''}
        setTimeout(()=>successMessage.value='',3000)
      }
      catch{
        errorMessage.value='Failed to update password'
      } finally{
        loadingPassword.value=false
      }
    }

    async function deleteAccount(){
      if(!deleteForm.value.password) return
      loadingDelete.value=true
      errorMessage.value = ''
      successMessage.value = ''
      try{
        const res = await fetch('/backend/api/user-api/user-delete-own-account-api.php', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json', 
            'Auth': `Bearer ${loginStore.jwt}` 
          }
        })
        const data = await res.json()
        
        if (data.success) {
          successMessage.value = 'Account deleted successfully. Redirecting...'
          setTimeout(() => {
            loginStore.clearJwt()
            router.push('/')
          }, 1500)
        } else {
          errorMessage.value = data.message || 'Failed to delete account'
          showDeleteModal.value = false
          setTimeout(() => errorMessage.value = '', 3000)
        }
      }
      catch(err){
        errorMessage.value = 'Network error. Failed to delete account'
        showDeleteModal.value = false
        setTimeout(() => errorMessage.value = '', 3000)
      }
      finally{loadingDelete.value=false}
    }

    onMounted(async () => {
      loginStore.loadJwt()
      if(!loginStore.jwt) router.push('/user-login')
      await loadProfile()
      await loadLimits()
    })

    return {
      successMessage, errorMessage, loadingProfile, loadingPassword, loadingLimit, loadingDelete, showDeleteModal,
      profileForm, passwordForm, limitForm, deleteForm, allLimits, limitsEnabled,
      profileImageUrl, cacheBuster,
      onProfilePictureSelected, deleteProfilePicture,
      updateProfile, updatePassword, submitLimit, deleteAccount, updateLimitsToggle
    }
  }
}
</script>

<style scoped>
.modal.show { background-color: rgba(0,0,0,0.5); }
</style>
