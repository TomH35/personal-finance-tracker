<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useLoginStore } from '@/stores/loginStore'
import { authenticatedFetch } from '@/utils/api'

const loginStore = useLoginStore()

// Users
const users = ref([])
const userError = ref('')

const newUser = ref({ username: '', email: '', password: '', role: 'user' })
const editUserData = ref({ user_id: null, username: '', email: '', role: 'user' })

// Password requirements validation for new user
const newUserPasswordRequirements = computed(() => {
  const password = newUser.value.password
  return {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
  }
})

const newUserPasswordRequirementsMet = computed(() => {
  const reqs = newUserPasswordRequirements.value
  return reqs.length && reqs.uppercase && reqs.lowercase && reqs.number && reqs.special
})

// Categories
const categories = ref([])
const newCategory = ref('')
const newCategoryType = ref('expense')
const selectedCategory = ref({ name: '', type: 'expense' })
const categoryError = ref('')

// Alert
const alertMessage = ref('')          // Message text
const alertVisible = ref(false)

const showAlert = (message, duration = 3000) => {
  alertMessage.value = message
  alertVisible.value = true

  // Hide after a delay
  setTimeout(() => {
    alertVisible.value = false
    alertMessage.value = ''
  }, duration)
}

// Fetch users and categories
onMounted(async () => {
  try {
    const response = await authenticatedFetch('/backend/api/user-api/user-get-all-api.php')
    const data = await response.json()
    if (data.success) users.value = data.users
    else userError.value = data.message
  } catch (err) {
    userError.value = 'Network error: ' + err.message
  }

  getCategories()
})

// OPEN "ADD USER" modal
const openAddUserModal = async () => {
  newUser.value = { username: '', email: '', password: '', role: 'user' }
  await nextTick()
  new bootstrap.Modal(document.getElementById('addUserModal')).show()
}

// CREATE USER
const createUser = async () => {
  // Validate password requirements
  if (!newUserPasswordRequirementsMet.value) {
    alert('Password does not meet security requirements. Please ensure it meets all requirements listed.')
    return
  }

  try {
    // Use the correct API based on selected role
    const apiUrl = newUser.value.role === 'admin'
      ? '/backend/api/auth-api/admin-registration-api.php'
      : '/backend/api/auth-api/user-registration-api.php'

    const res = await authenticatedFetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify(newUser.value)
    })

    const data = await res.json()

    if (data.success) {
      users.value.push({ user_id: data.user_id, username: newUser.value.username, email: newUser.value.email, role: newUser.value.role })
      bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide()
      showAlert(`User ${newUser.value.username} created successfully!`)
    } else {
      alert(data.message)
    }

  } catch (err) {
    alert('Error: ' + err.message)
  }
}

// OPEN EDIT USER MODAL
const openEditUserModal = async (user) => {
  editUserData.value = { ...user }
  await nextTick()
  new bootstrap.Modal(document.getElementById('editUserModal')).show()
}

// UPDATE USER
const updateUser = async () => {
  try {
    const res = await authenticatedFetch('/backend/api/user-api/user-edit-api.php', {
      method: 'POST',
      body: JSON.stringify(editUserData.value)
    })

    const data = await res.json()
    if (data.success) {
      const index = users.value.findIndex(u => u.user_id === editUserData.value.user_id)
      const user = users.value.find(u => u.user_id === editUserData.value.user_id)
      if (index !== -1) users.value[index] = { ...editUserData.value }
      bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide()
      showAlert(`User ${user.username} updated successfully!`)
    } else alert(data.message)

  } catch (err) {
    alert('Error: ' + err.message)
  }
}

const deleteUser = async (user_id) => {
  const user = users.value.find(u => u.user_id === user_id)
  if (!confirm('Are you sure you want to delete this user?')) return

  try {
    const res = await authenticatedFetch('/backend/api/user-api/user-delete-api.php', {
      method: 'POST',
      body: JSON.stringify({ user_id })
    })

    const data = await res.json()
    if (data.success) {
      users.value = users.value.filter(u => u.user_id !== user_id)
      showAlert(`User ${user.username} deleted successfully!`)
    } else alert(data.message)

  } catch (err) {
    alert('Error: ' + err.message)
  }
}

// Category actions
const getCategories = async () => {
  try {
    const res = await authenticatedFetch('/backend/api/category-api/category-get-all-api.php')
    const data = await res.json()
    if (data.success) categories.value = data.categories
    else categoryError.value = data.message
  } catch (err) {
    categoryError.value = 'Network error: ' + err.message
  }
}

const addCategory = async () => {
  if (!newCategory.value) return

  const name = newCategory.value.trim().toLowerCase()
  const duplicate = categories.value.some(c => c.name.toLowerCase() === name)
  if (duplicate) {
    alert('Category name already exists')
    return
  }

  try {
    const res = await authenticatedFetch('/backend/api/category-api/category-create-api.php', {
      method: 'POST',
      body: JSON.stringify({ name: newCategory.value.trim(), type: newCategoryType.value })
    })
    const data = await res.json()
    if (data.success) {
      categories.value.push(data.category)
      newCategory.value = ''
      newCategoryType.value = 'expense'
    } else alert(data.message)
  } catch (err) {
    alert('Network error: ' + err.message)
  }
}

const openEditCategoryModal = async (category) => {
  selectedCategory.value = { ...category }
  await nextTick()
  const modalEl = document.getElementById('editCategoryModal')
  const modal = new bootstrap.Modal(modalEl)
  modal.show()
}

const updateCategory = async () => {
  const name = selectedCategory.value.name.trim().toLowerCase()
  const duplicate = categories.value.some(
    c => c.name.toLowerCase() === name && c.id !== selectedCategory.value.id
  )
  if (duplicate) {
    alert('Category name already exists')
    return
  }

  try {
    const res = await authenticatedFetch('/backend/api/category-api/category-edit-api.php', {
      method: 'POST',
      body: JSON.stringify(selectedCategory.value)
    })
    const data = await res.json()
    if (data.success) {
      const index = categories.value.findIndex(c => c.id === selectedCategory.value.id)
      if (index !== -1) categories.value[index] = { ...selectedCategory.value }

      await nextTick()
      const modalEl = document.getElementById('editCategoryModal')
      const modal = bootstrap.Modal.getInstance(modalEl)
      modal.hide()
    } else alert(data.message)
  } catch (err) {
    alert('Network error: ' + err.message)
  }
}

const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category?')) return
  try {
    const res = await authenticatedFetch('/backend/api/category-api/category-delete-api.php', {
      method: 'POST',
      body: JSON.stringify({ id })
    })
    const data = await res.json()
    if (data.success) categories.value = categories.value.filter(c => c.id !== id)
    else alert(data.message)
  } catch (err) {
    alert('Network error: ' + err.message)
  }
}
</script>

<template>
  <div class="container-fluid">
    <div class="row">

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-white border-end min-vh-100 p-3">
        <h5 class="text-primary mb-4">Dashboard</h5>
        <div class="nav flex-column nav-pills" id="adminTab" role="tablist" aria-orientation="vertical">
          <button class="nav-link active mb-2 text-start" id="users-tab" data-bs-toggle="tab" data-bs-target="#users"
            type="button" role="tab">👥 Users</button>
          <button class="nav-link mb-2 text-start" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories"
            type="button" role="tab">🗂️ Categories</button>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <h2 class="fw-semibold mb-4">Admin Panel</h2>

        <div class="tab-content" id="adminTabContent">

          <!-- USERS -->
          <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="card-title mb-0">Users List</h5>
                  <button class="btn btn-sm btn-primary" @click="openAddUserModal">+ Add User</button>
                </div>
                <div class="table-responsive">
                  <table class="table align-middle table-hover">
                    <thead class="table-primary">
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="user in users" :key="user.user_id">
                        <td>{{ user.user_id }}</td>
                        <td>{{ user.username }}</td>
                        <td>{{ user.email }}</td>
                        <td v-if="user.role == 'admin'"><span class="badge bg-success">Admin</span></td>
                        <td v-else><span class="badge bg-secondary">User</span></td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1"
                            @click="openEditUserModal(user)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger"
                            @click="deleteUser(user.user_id)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-if="alertVisible" class="alert alert-success" role="alert">
                  {{ alertMessage }}
                </div>
              </div>
            </div>
          </div>

          <!-- CATEGORIES -->
          <div class="tab-pane fade" id="categories" role="tabpanel">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="card-title mb-0">Categories</h5>
                  <div class="input-group" style="max-width: 400px;">
                    <input v-model="newCategory" type="text" class="form-control" placeholder="New category">
                    <select v-model="newCategoryType" class="form-select">
                      <option value="expense">Expense</option>
                    </select>
                    <button class="btn btn-primary" @click="addCategory">Add</button>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table align-middle table-hover">
                    <thead class="table-primary">
                      <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Type</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="category in categories" :key="category.id">
                        <td>{{ category.id }}</td>
                        <td>{{ category.name }}</td>
                        <td>
                          <span v-if="category.type === 'expense'" class="badge bg-danger">Expense</span>
                        </td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1"
                            @click="openEditCategoryModal(category)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger"
                            @click="deleteCategory(category.id)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <p v-if="categoryError" class="text-danger mt-2">{{ categoryError }}</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editCategoryLabel">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input v-model="selectedCategory.name" class="form-control mb-2" placeholder="Category name" />
            <select v-model="selectedCategory.type" class="form-select">
              <option value="expense">Expense</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="updateCategory">Save changes</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add User</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input v-model="newUser.username" class="form-control mb-2" placeholder="Username" />
            <input v-model="newUser.email" class="form-control mb-2" placeholder="Email" />
            <input 
              v-model="newUser.password" 
              type="password" 
              class="form-control mb-2" 
              :class="{ 'is-invalid': newUser.password && !newUserPasswordRequirementsMet }"
              placeholder="Password" 
            />
            <!-- Password Requirements -->
            <div v-if="newUser.password" class="small mb-2">
              <div :class="newUserPasswordRequirements.length ? 'text-success' : 'text-danger'">
                <i :class="newUserPasswordRequirements.length ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                At least 8 characters
              </div>
              <div :class="newUserPasswordRequirements.uppercase ? 'text-success' : 'text-danger'">
                <i :class="newUserPasswordRequirements.uppercase ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                At least one uppercase letter (A-Z)
              </div>
              <div :class="newUserPasswordRequirements.lowercase ? 'text-success' : 'text-danger'">
                <i :class="newUserPasswordRequirements.lowercase ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                At least one lowercase letter (a-z)
              </div>
              <div :class="newUserPasswordRequirements.number ? 'text-success' : 'text-danger'">
                <i :class="newUserPasswordRequirements.number ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                At least one number (0-9)
              </div>
              <div :class="newUserPasswordRequirements.special ? 'text-success' : 'text-danger'">
                <i :class="newUserPasswordRequirements.special ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
              </div>
            </div>
            <select v-model="newUser.role" class="form-select">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="createUser">Create</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit User</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input v-model="editUserData.username" class="form-control mb-2" placeholder="Username" />
            <input v-model="editUserData.email" class="form-control mb-2" placeholder="Email" />
            <select v-model="editUserData.role" class="form-select">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="updateUser">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
