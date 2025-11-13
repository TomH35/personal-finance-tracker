<template>
  <div class="bg-light min-vh-100">
    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-white border-end min-vh-100 p-3">
          <h5 class="text-primary mb-4">Account Overview</h5>
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Summary</h6>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                  Income <span class="badge bg-success">${{ totalIncome.toFixed(2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Expenses <span class="badge bg-danger">$0.00</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Balance <span class="badge bg-primary">${{ totalIncome.toFixed(2) }}</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Categories</h6>
              <ul class="list-group list-group-flush mb-3">
                <li 
                  v-for="cat in categories" 
                  :key="cat.id" 
                  class="list-group-item small d-flex justify-content-between align-items-center"
                >
                  <div>
                    <span>{{ cat.name }}</span>
                    <span class="badge ms-2" :class="cat.type === 'income' ? 'bg-success' : 'bg-danger'">{{ cat.type }}</span>
                  </div>
                  <div v-if="!cat.is_predefined">
                    <button class="btn btn-sm btn-outline-primary me-1" @click="openEditCategoryModal(cat)">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" @click="openDeleteCategoryModal(cat)">Delete</button>
                  </div>
                </li>
              </ul>
              <button class="btn btn-primary btn-sm w-100" @click="openCreateCategoryModal">Create Category</button>
            </div>
          </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
          <h3 class="fw-semibold mb-4">Dashboard</h3>

          <!-- Alert Messages -->
          <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ successMessage }}
            <button type="button" class="btn-close" @click="successMessage = ''"></button>
          </div>
          <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ errorMessage }}
            <button type="button" class="btn-close" @click="errorMessage = ''"></button>
          </div>

          <!-- Add Income -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Add Income</h6>
              <form @submit.prevent="addIncome()">
                <div class="row g-2">
                  <div class="col-md-3">
                    <input
                      type="number"
                      v-model="formData.amount"
                      class="form-control"
                      placeholder="Amount"
                      step="0.01"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <select v-model="formData.category_id" class="form-select" required>
                      <option value="">Select Category</option>
                      <option v-for="cat in incomeCategories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <input
                      type="date"
                      v-model="formData.date"
                      class="form-control"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <input
                      type="text"
                      v-model="formData.note"
                      class="form-control"
                      placeholder="Note (optional)"
                    />
                  </div>
                  <div class="col-12 mt-3">
                    <button class="btn btn-primary" :disabled="loading">
                      <span v-if="loading">Adding...</span>
                      <span v-else>Add Income</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Income Transactions -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Recent Income</h6>
                <button class="btn btn-sm btn-outline-primary" @click="loadIncome">
                  <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
              </div>
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-else-if="incomeList.length === 0" class="text-center py-4 text-muted">
                No income transactions yet. Add your first income above!
              </div>
              <div v-else class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-primary">
                    <tr>
                      <th>Date</th>
                      <th>Category</th>
                      <th>Note</th>
                      <th class="text-end">Amount</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="income in incomeList" :key="income.id">
                      <td>{{ income.date }}</td>
                      <td>{{ income.category_name }}</td>
                      <td>{{ income.note || '-' }}</td>
                      <td class="text-end text-success fw-bold">
                        +${{ parseFloat(income.amount).toFixed(2) }}
                      </td>
                      <td class="text-end">
                        <button 
                          class="btn btn-sm btn-warning me-2" 
                          @click="editIncomeItem(income)"
                          :disabled="loading"
                        >
                          Edit
                        </button>
                        <button 
                          class="btn btn-sm btn-danger" 
                          @click="deleteIncomeItem(income.id)"
                          :disabled="loading"
                        >
                          Delete
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Edit Income Modal -->
    <div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editIncomeModalLabel">Edit Income</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="updateIncome">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Amount</label>
                <input
                  type="number"
                  v-model="editFormData.amount"
                  class="form-control"
                  placeholder="Amount"
                  step="0.01"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <select v-model="editFormData.category_id" class="form-select" required>
                  <option value="">Select Category</option>
                  <option v-for="cat in incomeCategories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Date</label>
                <input
                  type="date"
                  v-model="editFormData.date"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Note (optional)</label>
                <input
                  type="text"
                  v-model="editFormData.note"
                  class="form-control"
                  placeholder="Note"
                />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                Update Income
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="createCategoryModalLabel">Create Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveNewCategory">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input
                  type="text"
                  v-model="newCategory"
                  class="form-control"
                  placeholder="Category name"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Type</label>
                <select v-model="newCategoryType" class="form-select" required>
                  <option value="expense">Expense</option>
                  <option value="income">Income</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="updateCategory">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input
                  type="text"
                  v-model="editCategoryData.name"
                  class="form-control"
                  placeholder="Category name"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Type</label>
                <select v-model="editCategoryData.type" class="form-select" required>
                  <option value="expense">Expense</option>
                  <option value="income">Income</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete the category "{{ editCategoryData.name }}"?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmDeleteCategory" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Limit Warning Modal -->
    <div class="modal fade" id="limitWarningModal" tabindex="-1" aria-labelledby="limitWarningModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-warning">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="limitWarningModalLabel">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Warning - Approaching Limit
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">
              Your monthly expenses are approaching the warning limit of <strong>${{ warningLimit }}</strong>.
              We recommend monitoring your expenses and considering reducing them.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-warning" data-bs-dismiss="modal">I Understand</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Limit Critical Modal -->
    <div class="modal fade" id="limitCriticalModal" tabindex="-1" aria-labelledby="limitCriticalModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-danger">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="limitCriticalModalLabel">
              <i class="bi bi-x-circle-fill me-2"></i>Critical - Limit Exceeded!
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">
              <strong>Warning!</strong> Your monthly expenses have exceeded the critical limit of <strong>${{ criticalLimit }}</strong>.
              We strongly recommend reviewing your expenses immediately!
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">I Understand</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useLoginStore } from '@/stores/loginStore'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'UserDashboardView',
  setup() {
    const loginStore = useLoginStore()
    const router = useRouter()

    const incomeList = ref([])
    const categories = ref([])
    const loading = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')
    const editingId = ref(null)

    // Limit state
    const limits = ref(null)
    const warningLimit = ref(0)
    const criticalLimit = ref(0)
    const limitsEnabled = ref(false)

    const formData = ref({
      amount: '',
      category_id: '',
      note: '',
      date: new Date().toISOString().split('T')[0]
    })
    const editFormData = ref({
      amount: '',
      category_id: '',
      note: '',
      date: ''
    })
    const newCategory = ref('')
    const newCategoryType = ref('income')
    const editCategoryData = ref({
      id: null,
      name: '',
      type: 'income'
    })

    // Computed
    const incomeCategories = computed(() => {
      return categories.value.filter(cat => cat.type === 'income')
    })
    
    const totalIncome = computed(() => {
      return incomeList.value.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
    })

    // API Methods
    async function loadIncome() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/income-api/income-get-all-api.php', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          }
        })
        const data = await response.json()
        if (data.success) {
          incomeList.value = data.income || []
        }
      } catch (err) {
        console.error('Failed to load income:', err)
      } finally {
        loading.value = false
      }
    }

    async function loadCategories() {
      try {
        const response = await fetch('/backend/api/category-api/category-get-all-api.php', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          }
        })
        const data = await response.json()
        if (data.success) {
          categories.value = data.categories || []
        }
      } catch (err) {
        console.error('Failed to load categories:', err)
      }
    }

    async function loadLimits() {
      try {
        const response = await fetch('/backend/api/limit-api/get-limit-api.php', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          }
        })
        const data = await response.json()
        if (data.success && data.limit) {
          limits.value = data.limit
          warningLimit.value = parseFloat(data.limit.warning_limit) || 0
          criticalLimit.value = parseFloat(data.limit.critical_limit) || 0
          limitsEnabled.value = data.limit.enabled === '1' || data.limit.enabled === 1
        }
      } catch (err) {
        console.error('Failed to load limits:', err)
      }
    }

    function checkLimits(monthlyExpenses) {
      if (!limitsEnabled.value) return

      if (monthlyExpenses >= criticalLimit.value) {
        const modal = new window.bootstrap.Modal(document.getElementById('limitCriticalModal'))
        modal.show()
      } else if (monthlyExpenses >= warningLimit.value) {
        const modal = new window.bootstrap.Modal(document.getElementById('limitWarningModal'))
        modal.show()
      }
    }

    async function addIncome() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/income-api/income-create-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            amount: formData.value.amount,
            category_id: formData.value.category_id,
            note: formData.value.note || null,
            date: formData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Income added successfully!'
          formData.value = {
            amount: '',
            category_id: '',
            note: '',
            date: new Date().toISOString().split('T')[0]
          }
          await loadIncome()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to add income'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to add income'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    async function deleteIncomeItem(id) {
      if (!confirm('Are you sure you want to delete this income?')) return

      loading.value = true
      try {
        const response = await fetch('/backend/api/income-api/income-delete-api.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ id })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Income deleted successfully!'
          await loadIncome()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to delete income'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to delete income'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    function editIncomeItem(income) {
      editingId.value = income.id
      editFormData.value = {
        amount: income.amount,
        category_id: income.category_id,
        note: income.note || '',
        date: income.date
      }
      // Open Bootstrap modal
      const modal = new window.bootstrap.Modal(document.getElementById('editIncomeModal'))
      modal.show()
    }

    async function updateIncome() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/income-api/income-edit-api.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            id: editingId.value,
            amount: editFormData.value.amount,
            category_id: editFormData.value.category_id,
            note: editFormData.value.note || null,
            date: editFormData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Income updated successfully!'
          // Close modal
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('editIncomeModal'))
          modal.hide()
          editingId.value = null
          await loadIncome()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to update income'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to update income'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    async function addCategory() {
      const name = newCategory.value.trim()
      const type = newCategoryType.value
      
      if (!name) {
        errorMessage.value = 'Category name is required'
        setTimeout(() => errorMessage.value = '', 3000)
        return
      }

      loading.value = true
      try {
        const response = await fetch('/backend/api/custom-categories-api/custom-category-create-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ name, type })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Category added successfully!'
          newCategory.value = ''
          newCategoryType.value = 'income'
          await loadCategories()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to add category'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to add category'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    function openCreateCategoryModal() {
      newCategory.value = ''
      newCategoryType.value = 'income'
      const modal = new window.bootstrap.Modal(document.getElementById('createCategoryModal'))
      modal.show()
    }

    async function saveNewCategory() {
      await addCategory()
      const modal = window.bootstrap.Modal.getInstance(document.getElementById('createCategoryModal'))
      if (modal) modal.hide()
    }

    function openEditCategoryModal(cat) {
      editCategoryData.value = {
        id: cat.id,
        name: cat.name,
        type: cat.type
      }
      const modal = new window.bootstrap.Modal(document.getElementById('editCategoryModal'))
      modal.show()
    }

    async function updateCategory() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/custom-categories-api/custom-category-edit-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            id: editCategoryData.value.id,
            name: editCategoryData.value.name,
            type: editCategoryData.value.type
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Category updated successfully!'
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'))
          modal.hide()
          await loadCategories()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to update category'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to update category'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    function openDeleteCategoryModal(cat) {
      editCategoryData.value = {
        id: cat.id,
        name: cat.name,
        type: cat.type
      }
      const modal = new window.bootstrap.Modal(document.getElementById('deleteCategoryModal'))
      modal.show()
    }

    async function confirmDeleteCategory() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/custom-categories-api/custom-category-delete-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ id: editCategoryData.value.id })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Category deleted successfully!'
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('deleteCategoryModal'))
          modal.hide()
          await loadCategories()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to delete category'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to delete category'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    // Load data on mount
    onMounted(async () => {
      loginStore.loadJwt()
      if (!loginStore.jwt) {
        router.push('/user-login')
        return
      }

      await loadCategories()
      await loadIncome()
      await loadLimits()
    })

    return {
      incomeList,
      categories,
      loading,
      successMessage,
      errorMessage,
      editingId,
      formData,
      editFormData,
      newCategory,
      newCategoryType,
      editCategoryData,
      incomeCategories,
      totalIncome,
      limits,
      warningLimit,
      criticalLimit,
      limitsEnabled,
      loadIncome,
      addIncome,
      editIncomeItem,
      updateIncome,
      deleteIncomeItem,
      loadLimits,
      checkLimits,
      addCategory,
      openCreateCategoryModal,
      saveNewCategory,
      openEditCategoryModal,
      updateCategory,
      openDeleteCategoryModal,
      confirmDeleteCategory
    }
  }
}
</script>
