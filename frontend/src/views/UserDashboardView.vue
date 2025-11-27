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
                  Income <span class="badge bg-success">{{ currencySymbol }}{{ totalIncome.toFixed(2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Expenses <span class="badge bg-danger">{{ currencySymbol }}{{ totalExpenses.toFixed(2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Balance <span class="badge" :class="balance >= 0 ? 'bg-primary' : 'bg-warning'">{{ currencySymbol }}{{ balance.toFixed(2) }}</span>
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

          <!-- Add Transaction -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Add Transaction</h6>
              <form @submit.prevent="addTransaction()">
                <div class="row g-2">
                  <div class="col-md-2">
                    <select v-model="formData.type" class="form-select" required>
                      <option value="income">Income</option>
                      <option value="expense">Expense</option>
                    </select>
                  </div>
                  <div class="col-md-2">
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
                      <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-2">
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
                      <span v-else>Add Transaction</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Recent Transactions</h6>
                <button class="btn btn-sm btn-outline-primary" @click="loadAllTransactions">
                  <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
              </div>

             
              <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-2">
                  <label class="form-label small mb-1">Type</label>
                  <select v-model="filterType" class="form-select form-select-sm">
                    <option value="all">All</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1">Category</label>
                  <select v-model="filterCategory" class="form-select form-select-sm">
                    <option value="all">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                      {{ cat.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1">Min Amount</label>
                  <input type="number" v-model="filterMinAmount" class="form-control form-control-sm" placeholder="0" step="0.01">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1">Max Amount</label>
                  <input type="number" v-model="filterMaxAmount" class="form-control form-control-sm" placeholder="∞" step="0.01">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1">Sort By</label>
                  <select v-model="sortBy" class="form-select form-select-sm">
                    <option value="date-desc">Date (Newest First)</option>
                    <option value="date-asc">Date (Oldest First)</option>
                    <option value="amount-desc">Amount (Highest First)</option>
                    <option value="amount-asc">Amount (Lowest First)</option>
                  </select>
                </div>
              </div>

              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-else-if="allTransactions.length === 0" class="text-center py-4 text-muted">
                No transactions yet. Add your first transaction above!
              </div>
              <div v-else-if="filteredAndSortedTransactions.length === 0" class="text-center py-4 text-muted">
                No transactions match your filters. Try adjusting the filters above.
              </div>
              <div v-else class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-primary">
                    <tr>
                      <th>Type</th>
                      <th>Date</th>
                      <th>Category</th>
                      <th>Note</th>
                      <th class="text-end">Amount</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="transaction in filteredAndSortedTransactions" :key="transaction.id">
                      <td>
                        <span class="badge" :class="transaction.type === 'income' ? 'bg-success' : 'bg-danger'">
                          {{ transaction.type === 'income' ? 'Income' : 'Expense' }}
                        </span>
                      </td>
                      <td>{{ transaction.date }}</td>
                      <td>{{ transaction.category_name }}</td>
                      <td>{{ transaction.note || '-' }}</td>
                      <td class="text-end fw-bold" :class="transaction.type === 'income' ? 'text-success' : 'text-danger'">
                        {{ transaction.type === 'income' ? '+' : '-' }}{{ currencySymbol }}{{ convertCurrency(parseFloat(transaction.amount)).toFixed(2) }}
                      </td>
                      <td class="text-end">
                        <button 
                          class="btn btn-sm btn-warning me-2" 
                          @click="editTransaction(transaction)"
                          :disabled="loading"
                        >
                          Edit
                        </button>
                        <button 
                          class="btn btn-sm btn-danger" 
                          @click="deleteTransaction(transaction.id, transaction.type)"
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

    <div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editIncomeModalLabel">Edit Transaction</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="updateTransaction">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Type</label>
                <select v-model="editFormData.type" class="form-select" required>
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
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
                  <option v-for="cat in editFilteredCategories" :key="cat.id" :value="cat.id">
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
                Update Transaction
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


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


    <div class="modal fade" id="limitWarningModal" tabindex="-1" aria-labelledby="limitWarningModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="limitWarningModalLabel">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Warning - Approaching Limit
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-2">
              Your monthly expenses of <strong>{{ currencySymbol }}{{ convertCurrency(currentExpenseAmount).toFixed(2) }}</strong> 
              have exceeded the warning limit of <strong>{{ currencySymbol }}{{ convertCurrency(warningLimit).toFixed(2) }}</strong> 
              by <strong class="text-warning">{{ currencySymbol }}{{ convertCurrency(limitOverage).toFixed(2) }}</strong>.
            </p>
            <p class="mb-0">
              We recommend monitoring your expenses and considering reducing them.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-warning" data-bs-dismiss="modal">I Understand</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="limitCriticalModal" tabindex="-1" aria-labelledby="limitCriticalModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="limitCriticalModalLabel">
              <i class="bi bi-x-circle-fill me-2"></i>Critical - Limit Exceeded!
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-2">
              <strong>Warning!</strong> Your monthly expenses of <strong>{{ currencySymbol }}{{ convertCurrency(currentExpenseAmount).toFixed(2) }}</strong> 
              have exceeded the critical limit of <strong>{{ currencySymbol }}{{ convertCurrency(criticalLimit).toFixed(2) }}</strong> 
              by <strong class="text-danger">{{ currencySymbol }}{{ convertCurrency(limitOverage).toFixed(2) }}</strong>!
            </p>
            <p class="mb-0">
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
import { useNotificationStore } from '@/stores/notificationStore'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'UserDashboardView',
  setup() {
    const loginStore = useLoginStore()
    const notificationStore = useNotificationStore()
    const router = useRouter()

    const incomeList = ref([])
    const expenseList = ref([])
    const allTransactions = ref([])
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
    const limitOverage = ref(0)
    const currentExpenseAmount = ref(0)
    const warningShownThisSession = ref(false)
    const criticalShownThisSession = ref(false)

    // Currency state
    const userCurrency = ref('USD')
    const currencySymbols = {
      'USD': '$',
      'EUR': '€',
      'PLN': 'zł',
      'CZK': 'Kč'
    }
    
    // Exchange rates (as of November 15, 2025) - base currency: USD
    const exchangeRates = {
      'USD': 1.0,
      'EUR': 0.92,      // 1 USD = 0.92 EUR
      'PLN': 4.05,      // 1 USD = 4.05 PLN
      'CZK': 23.15      // 1 USD = 23.15 CZK
    }
    
    const currencySymbol = computed(() => {
      return currencySymbols[userCurrency.value] || '$'
    })
    
    // Convert amount from USD to user's selected currency
    const convertCurrency = (amountInUSD) => {
      const rate = exchangeRates[userCurrency.value] || 1.0
      return amountInUSD * rate
    }

    const formData = ref({
      type: 'income',
      amount: '',
      category_id: '',
      note: '',
      date: new Date().toISOString().split('T')[0]
    })
    const editFormData = ref({
      type: 'income',
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

    const filterType = ref('all')
    const filterCategory = ref('all')
    const filterMinAmount = ref('')
    const filterMaxAmount = ref('')
    const sortBy = ref('date-desc')

    
    const incomeCategories = computed(() => {
      return categories.value.filter(cat => cat.type === 'income')
    })

    const filteredCategories = computed(() => {
      return categories.value.filter(cat => cat.type === formData.value.type)
    })

    const editFilteredCategories = computed(() => {
      return categories.value.filter(cat => cat.type === editFormData.value.type)
    })
    
    const totalIncome = computed(() => {
      const sum = incomeList.value.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      return convertCurrency(sum)
    })

    const totalExpenses = computed(() => {
      const sum = expenseList.value.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      return convertCurrency(sum)
    })

    const balance = computed(() => {
      return totalIncome.value - totalExpenses.value
    })

    // Calculate current month expenses in USD for limit checking
    const currentMonthExpensesUSD = computed(() => {
      const now = new Date()
      const currentMonth = now.getMonth()
      const currentYear = now.getFullYear()
      
      const monthExpenses = expenseList.value.filter(expense => {
        const expenseDate = new Date(expense.date)
        return expenseDate.getMonth() === currentMonth && 
               expenseDate.getFullYear() === currentYear
      })
      
      const total = monthExpenses.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      console.log('Current month expenses (USD):', total, 'from', monthExpenses.length, 'transactions')
      return total
    })

    
    const filteredAndSortedTransactions = computed(() => {
      let filtered = [...allTransactions.value]

      if (filterType.value !== 'all') {
        filtered = filtered.filter(t => t.type === filterType.value)
      }

      if (filterCategory.value !== 'all') {
        filtered = filtered.filter(t => t.category_id == filterCategory.value)
      }

      if (filterMinAmount.value !== '') {
        const min = parseFloat(filterMinAmount.value)
        filtered = filtered.filter(t => parseFloat(t.amount) >= min)
      }
      if (filterMaxAmount.value !== '') {
        const max = parseFloat(filterMaxAmount.value)
        filtered = filtered.filter(t => parseFloat(t.amount) <= max)
      }

    
      if (sortBy.value === 'date-desc') {
        filtered.sort((a, b) => {
          const dateCompare = new Date(b.date) - new Date(a.date)
          if (dateCompare !== 0) return dateCompare
          return b.id - a.id
        })
      } else if (sortBy.value === 'date-asc') {
        filtered.sort((a, b) => {
          const dateCompare = new Date(a.date) - new Date(b.date)
          if (dateCompare !== 0) return dateCompare
          return a.id - b.id
        })
      } else if (sortBy.value === 'amount-desc') {
        filtered.sort((a, b) => parseFloat(b.amount) - parseFloat(a.amount))
      } else if (sortBy.value === 'amount-asc') {
        filtered.sort((a, b) => parseFloat(a.amount) - parseFloat(b.amount))
      }

      return filtered
    })

   
    async function loadAllTransactions() {
      loading.value = true
      try {
        
        const response = await fetch('/backend/api/transaction-api/transaction-get-all-api.php', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          }
        })
        const data = await response.json()
        
        if (data.success) {
          const transactions = data.transactions || []
          
          
          incomeList.value = transactions.filter(t => t.type === 'income')
          expenseList.value = transactions.filter(t => t.type === 'expense')
          
          
          allTransactions.value = transactions.sort((a, b) => {
            const dateCompare = new Date(b.date) - new Date(a.date)
            if (dateCompare !== 0) return dateCompare
            return b.id - a.id
          })
        }
      } catch (err) {
        console.error('Failed to load transactions:', err)
      } finally {
        loading.value = false
      }
    }

    async function loadTransactions() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/transaction-api/transaction-get-all-api.php', {
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
        if (data.success && data.limit && data.limit.length > 0) {
          // Backend returns an array, get the first (most recent) limit
          const limitData = data.limit[0]
          limits.value = limitData
          warningLimit.value = parseFloat(limitData.warning_limit) || 0
          criticalLimit.value = parseFloat(limitData.critical_limit) || 0
          limitsEnabled.value = limitData.enabled === '1' || limitData.enabled === 1
          
          console.log('Limits loaded:', {
            warning: warningLimit.value,
            critical: criticalLimit.value,
            enabled: limitsEnabled.value
          })
        }
      } catch (err) {
        console.error('Failed to load limits:', err)
      }
    }

    function checkLimits(monthlyExpenses) {
      console.log('Checking limits:', {
        monthlyExpenses,
        warningLimit: warningLimit.value,
        criticalLimit: criticalLimit.value,
        enabled: limitsEnabled.value
      })
      
      if (!limitsEnabled.value) {
        console.log('Limits are disabled')
        return
      }

      currentExpenseAmount.value = monthlyExpenses

      // Check critical first - if critical is exceeded, only show critical modal
      if (monthlyExpenses >= criticalLimit.value) {
        if (!criticalShownThisSession.value) {
          limitOverage.value = monthlyExpenses - criticalLimit.value
          console.log('Critical limit reached! Over by:', limitOverage.value)
          criticalShownThisSession.value = true
          
          // Create notification
          notificationStore.addNotification(
            'critical',
            `Critical limit exceeded! You've spent ${currencySymbol.value}${monthlyExpenses.toFixed(2)} (over by ${currencySymbol.value}${limitOverage.value.toFixed(2)})`
          )
          
          const modal = new window.bootstrap.Modal(document.getElementById('limitCriticalModal'))
          modal.show()
        }
      } else if (monthlyExpenses >= warningLimit.value) {
        if (!warningShownThisSession.value) {
          limitOverage.value = monthlyExpenses - warningLimit.value
          console.log('Warning limit reached! Over by:', limitOverage.value)
          warningShownThisSession.value = true
          
          // Create notification
          notificationStore.addNotification(
            'warning',
            `Warning limit reached! You've spent ${currencySymbol.value}${monthlyExpenses.toFixed(2)} (over by ${currencySymbol.value}${limitOverage.value.toFixed(2)})`
          )
          
          const modal = new window.bootstrap.Modal(document.getElementById('limitWarningModal'))
          modal.show()
        }
      } else {
        console.log('Within limits')
        // Reset flags if user goes back under the limits
        warningShownThisSession.value = false
        criticalShownThisSession.value = false
      }
    }

    async function addTransaction() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/transaction-api/transaction-create-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            type: formData.value.type,
            amount: formData.value.amount,
            category_id: formData.value.category_id,
            note: formData.value.note || null,
            date: formData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction added successfully!'
          const wasExpense = formData.value.type === 'expense'
          formData.value = {
            type: 'income',
            amount: '',
            category_id: '',
            note: '',
            date: new Date().toISOString().split('T')[0]
          }
          await loadAllTransactions()
          
          // Check limits after adding expense
          if (wasExpense) {
            checkLimits(currentMonthExpensesUSD.value)
          }
          
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to add transaction'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to add transaction'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }
//  Delete Transaction
    async function deleteTransaction(id, type) {
      if (!confirm('Are you sure you want to delete this transaction?')) return

      loading.value = true
      try {
        const response = await fetch('/backend/api/transaction-api/transaction-delete-api.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ id, type })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction deleted successfully!'
          await loadAllTransactions()
          checkLimits(currentMonthExpensesUSD.value)
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to delete transaction'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to delete transaction'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    function editTransaction(transaction) {
      editingId.value = transaction.id
      editFormData.value = {
        type: transaction.type,
        amount: transaction.amount,
        category_id: transaction.category_id,
        note: transaction.note || '',
        date: transaction.date
      }
      
      const modal = new window.bootstrap.Modal(document.getElementById('editIncomeModal'))
      modal.show()
    }

    async function deleteTransactionItem(id) {
      if (!confirm('Are you sure you want to delete this transaction?')) return

      loading.value = true
      try {
        const response = await fetch('/backend/api/transaction-api/transaction-delete-api.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({ id })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction deleted successfully!'
          await loadTransactions()
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to delete transaction'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to delete transaction'
        setTimeout(() => errorMessage.value = '', 3000)
      } finally {
        loading.value = false
      }
    }

    function editTransactionItem(transaction) {
      editingId.value = transaction.id
      editFormData.value = {
        type: transaction.type || 'income',
        amount: transaction.amount,
        category_id: transaction.category_id,
        note: transaction.note || '',
        date: transaction.date
      }
     
      const modal = new window.bootstrap.Modal(document.getElementById('editIncomeModal'))
      modal.show()
    }

    async function updateTransaction() {
      loading.value = true
      try {
        const response = await fetch('/backend/api/transaction-api/transaction-edit-api.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Auth': `Bearer ${loginStore.jwt}`
          },
          body: JSON.stringify({
            id: editingId.value,
            type: editFormData.value.type,
            amount: editFormData.value.amount,
            category_id: editFormData.value.category_id,
            note: editFormData.value.note || null,
            date: editFormData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction updated successfully!'
          
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('editIncomeModal'))
          modal.hide()
          editingId.value = null
          await loadAllTransactions()
          checkLimits(currentMonthExpensesUSD.value)
          setTimeout(() => successMessage.value = '', 3000)
        } else {
          errorMessage.value = data.message || 'Failed to update transaction'
          setTimeout(() => errorMessage.value = '', 3000)
        }
      } catch (err) {
        errorMessage.value = 'Failed to update transaction'
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

    // Load user profile to get currency
    async function loadUserProfile() {
      try {
        const res = await fetch('/backend/api/user-api/user-get-profile-api.php', {
          headers: { 'Auth': `Bearer ${loginStore.jwt}` }
        })
        const data = await res.json()
        if (data.success && data.user) {
          userCurrency.value = data.user.currency || 'USD'
        }
      } catch (err) { 
        console.error('Failed to load user profile:', err)
      }
    }

    // Load data on mount
    onMounted(async () => {
      loginStore.loadJwt()
      if (!loginStore.jwt) {
        router.push('/user-login')
        return
      }

      await loadUserProfile()
      await loadCategories()
      await loadAllTransactions()
      await loadLimits()
    })

    return {
      incomeList,
      expenseList,
      allTransactions,
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
      filterType,
      filterCategory,
      filterMinAmount,
      filterMaxAmount,
      sortBy,
      incomeCategories,
      filteredCategories,
      editFilteredCategories,
      filteredAndSortedTransactions,
      totalIncome,
      totalExpenses,
      balance,
      limits,
      warningLimit,
      criticalLimit,
      limitsEnabled,
      limitOverage,
      currentExpenseAmount,
      currentMonthExpensesUSD,
      userCurrency,
      currencySymbol,
      convertCurrency,
      loadTransactions,
      loadAllTransactions,
      addTransaction,
      editTransactionItem,
      editTransaction,
      updateTransaction,
      deleteTransactionItem,
      deleteTransaction,
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
