<template>
  <div class="bg-light min-vh-100">
    <div class="container py-5">
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
                      {{ limitForm.limit_id ? 'Update Limit' : 'Save Limit' }}
                    </button>
                    <button v-if="limitForm.limit_id" type="button" class="btn btn-outline-secondary ms-2" @click="cancelEdit">Cancel Edit</button>
                  </div>
                </form>

                <hr class="my-4">

                <!-- Existing Limits -->
                <div v-if="allLimits.filter(l => l.enabled).length">
                  <h6 class="fw-semibold mb-3">Existing Limits</h6>
                  <div v-for="limit in allLimits.filter(l => l.enabled)" :key="limit.limit_id" class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
                    <div>
                      <strong>Warning: {{ limit.warning_limit }}, Critical: {{ limit.critical_limit }}</strong>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-outline-secondary me-2" @click="editLimit(limit)">Edit</button>
                      <button class="btn btn-sm btn-outline-danger" @click="deleteLimit(limit.limit_id)">Delete</button>
                    </div>
                  </div>
                </div>
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
    const limitForm = ref({ limit_id:null, warning_limit:0, critical_limit:0 })
    const deleteForm = ref({ password: '' })
    const allLimits = ref([])
    const limitsEnabled = ref(true)
    const userToggled = ref(false) // track if user clicked toggle

    async function loadProfile() {
      try {
        profileForm.value.name = 'John Doe'
        profileForm.value.email = 'john@example.com'
      } catch (err) { console.error(err) }
    }

    async function loadLimits() {
      try {
        const res = await fetch(`/backend/api/limit-api/get-limit-api.php`, {
          headers: { 'auth': `Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if (data.success && data.limit) {
          allLimits.value = Array.isArray(data.limit) ? data.limit : [data.limit]
          if (!userToggled.value) {
            limitsEnabled.value = allLimits.value.some(l => l.enabled)
          }
        }
      } catch (err) { console.error(err) }
    }

    async function updateLimitsToggle() {
      userToggled.value = true
      try {
        const res = await fetch(`/backend/api/limit-api/toggle-limit-api.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'auth': `Bearer ${loginStore.jwt}` },
          body: JSON.stringify({ enabled: limitsEnabled.value ? 1 : 0 })
        })
        const data = await res.json()
        if (!data.success) throw new Error(data.message)
        await loadLimits()
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
          body: JSON.stringify(limitForm.value)
        })
        const data = await res.json()
        if (data.success) {
          successMessage.value = data.message
          setTimeout(()=>successMessage.value='',3000)
          await loadLimits()
          cancelEdit()
        } else errorMessage.value = data.message
      } catch { errorMessage.value = 'Network error' }
      finally { loadingLimit.value = false }
    }

    function editLimit(limit) {
      limitForm.value = { ...limit }
    }

    function cancelEdit() {
      limitForm.value = { limit_id:null, warning_limit:0, critical_limit:0 }
    }

    async function deleteLimit(id) {
      loadingLimit.value = true
      try {
        const res = await fetch(`/backend/api/limit-api/delete-limit-api.php?limit_id=${id}`, {
          method: 'DELETE',
          headers: { 'auth': `Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if (data.success) {
          successMessage.value = data.message
          await loadLimits()
        } else errorMessage.value = data.message
      } catch { errorMessage.value = 'Network error' }
      finally { loadingLimit.value = false }
    }

    async function updateProfile() {
      loadingProfile.value = true
      errorMessage.value = ''
      successMessage.value = ''
      try { await new Promise(r => setTimeout(r, 1000)); successMessage.value='Profile updated successfully!'; setTimeout(()=>successMessage.value='',3000) }
      catch { errorMessage.value='Failed to update profile' }
      finally { loadingProfile.value = false }
    }

    async function updatePassword() {
      if(passwordForm.value.newPassword !== passwordForm.value.confirmPassword){errorMessage.value='New passwords do not match'; setTimeout(()=>errorMessage.value='',3000); return}
      loadingPassword.value=true
      try{await new Promise(r=>setTimeout(r,1000)); successMessage.value='Password updated successfully!'; passwordForm.value={currentPassword:'',newPassword:'',confirmPassword:''}; setTimeout(()=>successMessage.value='',3000)}
      catch{errorMessage.value='Failed to update password'} finally{loadingPassword.value=false}
    }

    async function deleteAccount(){
      if(!deleteForm.value.password) return
      loadingDelete.value=true
      try{await new Promise(r=>setTimeout(r,1000)); loginStore.clearJwt(); router.push('/user-login')}
      catch{errorMessage.value='Failed to delete account'}
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
      updateProfile, updatePassword, submitLimit, deleteAccount, editLimit, deleteLimit, cancelEdit, updateLimitsToggle
    }
  }
}
</script>

<style scoped>
.modal.show { background-color: rgba(0, 0, 0, 0.5); }
</style>
