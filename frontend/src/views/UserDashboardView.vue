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
              <h6 class="fw-semibold mb-3">Income Categories</h6>
              <ul class="list-group list-group-flush mb-3">
                <li v-for="cat in incomeCategories" :key="cat.id" class="list-group-item small">
                  {{ cat.name }}
                </li>
              </ul>
              <div class="input-group input-group-sm">
                <input
                  type="text"
                  v-model="newCategory"
                  class="form-control"
                  placeholder="New income category"
                  @keyup.enter="addCategory"
                />
                <button class="btn btn-primary" @click="addCategory">Add</button>
              </div>
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

          <!-- Add/Edit Income -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">{{ editingId ? 'Edit Income' : 'Add Income' }}</h6>
              <form @submit.prevent="editingId ? updateIncome() : addIncome()">
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
                      <span v-if="loading">{{ editingId ? 'Updating...' : 'Adding...' }}</span>
                      <span v-else>{{ editingId ? 'Update Income' : 'Add Income' }}</span>
                    </button>
                    <button v-if="editingId" type="button" class="btn btn-secondary ms-2" @click="cancelEdit">
                      Cancel
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
  </div>
</template>

<script>
import { useLoginStore } from '@/stores/loginStore'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const API_BASE_URL = 'http://localhost/personal-finance-tracker/backend/api'

export default {
  name: 'UserDashboardView',
  setup() {
    const loginStore = useLoginStore()
    const router = useRouter()

    const incomeList = ref([])
    const categories = ref([])
    const loading = ref(false)
    const newCategory = ref('')
    const successMessage = ref('')
    const errorMessage = ref('')
    const editingId = ref(null)
    const formData = ref({
      amount: '',
      category_id: '',
      note: '',
      date: new Date().toISOString().split('T')[0]
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
        const response = await fetch(`${API_BASE_URL}/income-api/income-get-all-api.php`, {
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
        const response = await fetch(`${API_BASE_URL}/category-api/category-get-all-api.php`, {
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

    async function addCategory() {
      const name = newCategory.value.trim()
      if (!name) return

      try {
        const response = await fetch(`${API_BASE_URL}/category-api/category-create-api.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ name, type: 'income' })
        })
        const data = await response.json()
        
        if (data.success) {
          successMessage.value = 'Category added successfully!'
          newCategory.value = ''
          await loadCategories()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to add category'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to add category'
        setTimeout(() => errorMessage.value = '', 3000)
      }
    }

    async function addIncome() {
      loading.value = true
      try {
        const response = await fetch(`${API_BASE_URL}/income-api/income-create-api.php`, {
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
        const response = await fetch(`${API_BASE_URL}/income-api/income-delete-api.php`, {
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
      formData.value = {
        amount: income.amount,
        category_id: income.category_id,
        note: income.note || '',
        date: income.date
      }
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    async function updateIncome() {
      loading.value = true
      try {
        const response = await fetch(`${API_BASE_URL}/income-api/income-edit-api.php`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            id: editingId.value,
            amount: formData.value.amount,
            category_id: formData.value.category_id,
            note: formData.value.note || null,
            date: formData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Income updated successfully!'
          cancelEdit()
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

    function cancelEdit() {
      editingId.value = null
      formData.value = {
        amount: '',
        category_id: '',
        note: '',
        date: new Date().toISOString().split('T')[0]
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
    })

    return {
      incomeList,
      categories,
      loading,
      newCategory,
      successMessage,
      errorMessage,
      editingId,
      formData,
      incomeCategories,
      totalIncome,
      loadIncome,
      addCategory,
      addIncome,
      editIncomeItem,
      updateIncome,
      cancelEdit,
      deleteIncomeItem
    }
  }
}
</script>
