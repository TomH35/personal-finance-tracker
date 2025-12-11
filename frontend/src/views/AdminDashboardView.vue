<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useLoginStore } from '@/stores/loginStore'
import { authenticatedFetch } from '@/utils/api'

const loginStore = useLoginStore()

const currencySymbols = {
  USD: '$',
  EUR: '€',
  PLN: 'zł',
  CZK: 'Kč'
}

const exchangeRates = {
  USD: 1.0,
  EUR: 0.92,
  PLN: 4.05,
  CZK: 23.15
}

const getCurrencySymbol = (currency) => currencySymbols[currency] || '$'
const getCurrencyRate = (currency) => exchangeRates[currency] || 1.0
const convertUsdToCurrency = (amountUSD = 0, currency = 'USD') => {
  return Number(amountUSD || 0) * getCurrencyRate(currency)
}
const convertCurrencyToUsd = (amount = 0, currency = 'USD') => {
  const rate = getCurrencyRate(currency)
  return rate === 0 ? 0 : Number(amount || 0) / rate
}
const formatAmountForCurrency = (amountUSD = 0, currency = 'USD') => {
  const value = convertUsdToCurrency(amountUSD, currency)
  return `${getCurrencySymbol(currency)}${value.toFixed(2)}`
}

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

// Tips
const tips = ref([])
const newTip = ref({ title: '', content: '' })
const editTipData = ref({ id: null, title: '', content: '' })
const tipError = ref('')

// User data management (transactions + categories)
const getDefaultTransactionForm = () => ({
  id: null,
  amount: '',
  category_id: '',
  note: '',
  date: '',
  type: 'expense',
  userCurrency: 'USD'
})

const getDefaultCategoryForm = () => ({
  id: null,
  name: '',
  type: 'expense'
})

const selectedUserForData = ref(null)
const selectedUserCurrency = ref('USD')
const selectedUserSymbol = ref('$')
const dataModalTab = ref('transactions')
const getTodayDate = () => new Date().toISOString().split('T')[0]
const getNewTransactionTemplate = () => ({
  amount: '',
  category_id: '',
  note: '',
  date: getTodayDate(),
  type: 'expense',
  userCurrency: selectedUserCurrency.value || 'USD'
})
const getNewCategoryTemplate = () => ({
  name: '',
  type: 'expense'
})
const userTransactions = ref([])
const userCategories = ref([])
const userCategoryOptions = ref([])
const transactionLoading = ref(false)
const categoryLoading = ref(false)
const transactionError = ref('')
const categoryManagementError = ref('')
const transactionForm = ref(getDefaultTransactionForm())
const newTransactionForm = ref(getNewTransactionTemplate())
const categoryForm = ref(getDefaultCategoryForm())
const newCategoryForm = ref(getNewCategoryTemplate())

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

const updateSelectedUserCurrency = (currency) => {
  const normalized = exchangeRates[currency] ? currency : 'USD'
  selectedUserCurrency.value = normalized
  selectedUserSymbol.value = getCurrencySymbol(normalized)
  newTransactionForm.value.userCurrency = normalized
}

const formatTransactionAmount = (amountUSD) => {
  return formatAmountForCurrency(amountUSD, selectedUserCurrency.value || 'USD')
}

const resetTransactionForm = () => {
  transactionForm.value = {
    ...getDefaultTransactionForm(),
    userCurrency: selectedUserCurrency.value || 'USD'
  }
}

const resetCategoryForm = () => {
  categoryForm.value = getDefaultCategoryForm()
}

const resetNewTransactionForm = () => {
  newTransactionForm.value = getNewTransactionTemplate()
}

const resetNewCategoryForm = () => {
  newCategoryForm.value = getNewCategoryTemplate()
}

const fetchUserTransactions = async (userId) => {
  if (!userId) return
  transactionLoading.value = true
  transactionError.value = ''
  try {
    const res = await authenticatedFetch(`/backend/api/admin-api/user-transactions-get-api.php?user_id=${userId}`)
    const data = await res.json()
    if (data.success) userTransactions.value = data.transactions || []
    else transactionError.value = data.message || 'Failed to load transactions.'
  } catch (err) {
    transactionError.value = 'Network error: ' + err.message
  } finally {
    transactionLoading.value = false
  }
}

const fetchUserCategories = async (userId) => {
  if (!userId) return
  categoryLoading.value = true
  categoryManagementError.value = ''
  try {
    const res = await authenticatedFetch(`/backend/api/admin-api/user-categories-get-api.php?user_id=${userId}`)
    const data = await res.json()
    if (data.success) {
      userCategoryOptions.value = data.categories || []
      userCategories.value = data.user_categories || []
    } else {
      categoryManagementError.value = data.message || 'Failed to load categories.'
    }
  } catch (err) {
    categoryManagementError.value = 'Network error: ' + err.message
  } finally {
    categoryLoading.value = false
  }
}

const openUserDataModal = async (user) => {
  if (!user) return
  selectedUserForData.value = user
  updateSelectedUserCurrency(user.currency)
  dataModalTab.value = 'transactions'
  userTransactions.value = []
  userCategories.value = []
  userCategoryOptions.value = []
  transactionError.value = ''
  categoryManagementError.value = ''
  resetTransactionForm()
  resetCategoryForm()
  resetNewTransactionForm()
  resetNewCategoryForm()
  await nextTick()
  const modalEl = document.getElementById('userDataModal')
  if (!modalEl) return
  const modal = new bootstrap.Modal(modalEl)
  modal.show()
  fetchUserTransactions(user.user_id)
  fetchUserCategories(user.user_id)
}

const startEditingTransaction = (transaction) => {
  if (!transaction) return
  const amountUSD = Number(transaction.amount ?? 0)
  const convertedAmount = convertUsdToCurrency(amountUSD, selectedUserCurrency.value || 'USD')
  transactionForm.value = {
    id: transaction.id,
    amount: convertedAmount ? convertedAmount.toFixed(2) : '',
    category_id: transaction.category_id,
    note: transaction.note || '',
    date: transaction.date ? transaction.date.substring(0, 10) : '',
    type: transaction.type || 'expense',
    userCurrency: selectedUserCurrency.value || 'USD'
  }
}

const cancelTransactionEdit = () => {
  resetTransactionForm()
}

const submitTransactionUpdate = async () => {
  if (!selectedUserForData.value || !transactionForm.value.id) return
  transactionError.value = ''
  const amountValue = Number(transactionForm.value.amount)

  if (!amountValue || Number.isNaN(amountValue)) {
    transactionError.value = 'Amount is required.'
    return
  }
  const categoryIdNumber = Number(transactionForm.value.category_id)
  if (!categoryIdNumber) {
    transactionError.value = 'Category selection is required.'
    return
  }
  if (!transactionForm.value.date) {
    transactionError.value = 'Date is required.'
    return
  }

  try {
    const amountInUsd = convertCurrencyToUsd(amountValue, selectedUserCurrency.value || 'USD')
    const requestPayload = {
      user_id: selectedUserForData.value.user_id,
      id: transactionForm.value.id,
      amount: Number(amountInUsd.toFixed(2)),
      category_id: categoryIdNumber,
      note: transactionForm.value.note,
      date: transactionForm.value.date,
      type: transactionForm.value.type,
      userCurrency: 'USD'
    }
    const res = await authenticatedFetch('/backend/api/admin-api/user-transaction-update-api.php', {
      method: 'PUT',
      body: JSON.stringify(requestPayload)
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserTransactions(selectedUserForData.value.user_id)
      resetTransactionForm()
      showAlert('Transaction updated successfully!')
    } else {
      transactionError.value = data.message || 'Failed to update transaction.'
    }
  } catch (err) {
    transactionError.value = 'Network error: ' + err.message
  }
}

const createTransactionForUser = async () => {
  if (!selectedUserForData.value) return
  transactionError.value = ''
  const amountValue = Number(newTransactionForm.value.amount)
  if (!amountValue || Number.isNaN(amountValue)) {
    transactionError.value = 'Amount is required.'
    return
  }
  const categoryIdNumber = Number(newTransactionForm.value.category_id)
  if (!categoryIdNumber) {
    transactionError.value = 'Category selection is required.'
    return
  }
  if (!newTransactionForm.value.date) {
    transactionError.value = 'Date is required.'
    return
  }

  try {
    const amountInUsd = convertCurrencyToUsd(amountValue, selectedUserCurrency.value || 'USD')
    const res = await authenticatedFetch('/backend/api/admin-api/user-transaction-create-api.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: selectedUserForData.value.user_id,
        amount: Number(amountInUsd.toFixed(2)),
        category_id: categoryIdNumber,
        note: newTransactionForm.value.note,
        date: newTransactionForm.value.date,
        type: newTransactionForm.value.type,
        userCurrency: 'USD'
      })
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserTransactions(selectedUserForData.value.user_id)
      resetNewTransactionForm()
      showAlert('Transaction added successfully!')
    } else {
      transactionError.value = data.message || 'Failed to add transaction.'
    }
  } catch (err) {
    transactionError.value = 'Network error: ' + err.message
  }
}

const deleteUserTransaction = async (transaction) => {
  if (!selectedUserForData.value || !transaction) return
  if (!confirm('Are you sure you want to delete this transaction?')) return
  try {
    const res = await authenticatedFetch('/backend/api/admin-api/user-transaction-delete-api.php', {
      method: 'DELETE',
      body: JSON.stringify({
        user_id: selectedUserForData.value.user_id,
        id: transaction.id,
        type: transaction.type
      })
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserTransactions(selectedUserForData.value.user_id)
      if (transactionForm.value.id === transaction.id) resetTransactionForm()
      showAlert('Transaction deleted successfully!')
    } else {
      transactionError.value = data.message || 'Failed to delete transaction.'
    }
  } catch (err) {
    transactionError.value = 'Network error: ' + err.message
  }
}

const startEditingCategory = (category) => {
  if (!category) return
  categoryForm.value = {
    id: category.id,
    name: category.name,
    type: category.type || 'expense'
  }
}

const cancelCategoryEdit = () => {
  resetCategoryForm()
}

const submitCategoryUpdate = async () => {
  if (!selectedUserForData.value || !categoryForm.value.id) return
  const trimmedName = categoryForm.value.name.trim()
  if (!trimmedName) {
    categoryManagementError.value = 'Category name is required.'
    return
  }
  try {
    const res = await authenticatedFetch('/backend/api/admin-api/user-category-update-api.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: selectedUserForData.value.user_id,
        id: categoryForm.value.id,
        name: trimmedName,
        type: categoryForm.value.type
      })
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserCategories(selectedUserForData.value.user_id)
      resetCategoryForm()
      showAlert('Category updated successfully!')
    } else {
      categoryManagementError.value = data.message || 'Failed to update category.'
    }
  } catch (err) {
    categoryManagementError.value = 'Network error: ' + err.message
  }
}

const createCategoryForUser = async () => {
  if (!selectedUserForData.value) return
  const trimmedName = newCategoryForm.value.name.trim()
  if (!trimmedName) {
    categoryManagementError.value = 'Category name is required.'
    return
  }
  try {
    const res = await authenticatedFetch('/backend/api/admin-api/user-category-create-api.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: selectedUserForData.value.user_id,
        name: trimmedName,
        type: newCategoryForm.value.type
      })
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserCategories(selectedUserForData.value.user_id)
      resetNewCategoryForm()
      showAlert('Category created successfully!')
    } else {
      categoryManagementError.value = data.message || 'Failed to create category.'
    }
  } catch (err) {
    categoryManagementError.value = 'Network error: ' + err.message
  }
}

const deleteUserCategory = async (category) => {
  if (!selectedUserForData.value || !category) return
  if (!confirm('Are you sure you want to delete this category?')) return
  try {
    const res = await authenticatedFetch('/backend/api/admin-api/user-category-delete-api.php', {
      method: 'POST',
      body: JSON.stringify({
        user_id: selectedUserForData.value.user_id,
        id: category.id
      })
    })
    const data = await res.json()
    if (data.success) {
      await fetchUserCategories(selectedUserForData.value.user_id)
      if (categoryForm.value.id === category.id) resetCategoryForm()
      showAlert('Category deleted successfully!')
    } else {
      categoryManagementError.value = data.message || 'Failed to delete category.'
    }
  } catch (err) {
    categoryManagementError.value = 'Network error: ' + err.message
  }
}

// Fetch users and categories
onMounted(async () => {
  try {
    const response = await authenticatedFetch('/backend/api/admin-api/user-get-all-api.php')
    const data = await response.json()
    if (data.success) users.value = data.users
    else userError.value = data.message
  } catch (err) {
    userError.value = 'Network error: ' + err.message
  }

  getCategories()
  getTips()
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
      users.value.push({
        user_id: data.user_id,
        username: newUser.value.username,
        email: newUser.value.email,
        role: newUser.value.role,
        currency: 'USD'
      })
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
    const res = await authenticatedFetch('/backend/api/admin-api/user-edit-api.php', {
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
    const res = await authenticatedFetch('/backend/api/admin-api/user-delete-api.php', {
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

// Tips actions
const getTips = async () => {
  try {
    const res = await authenticatedFetch('/backend/api/tips-api/tip-get-all-api.php')
    const data = await res.json()
    if (data.success) tips.value = data.tips
    else tipError.value = data.message
  } catch (err) {
    tipError.value = 'Network error: ' + err.message
  }
}

const createTip = async () => {
  if (!newTip.value.title || !newTip.value.content) {
    alert('Title and content are required')
    return
  }

  try {
    const res = await authenticatedFetch('/backend/api/tips-api/tip-create-api.php', {
      method: 'POST',
      body: JSON.stringify(newTip.value)
    })
    const data = await res.json()
    if (data.success) {
      tips.value.unshift(data.tip)
      newTip.value = { title: '', content: '' }
      showAlert('Tip created successfully!')
    } else alert(data.message)
  } catch (err) {
    alert('Network error: ' + err.message)
  }
}

const openEditTipModal = async (tip) => {
  editTipData.value = { ...tip }
  await nextTick()
  new bootstrap.Modal(document.getElementById('editTipModal')).show()
}

const updateTip = async () => {
  if (!editTipData.value.title || !editTipData.value.content) {
    alert('Title and content are required')
    return
  }

  try {
    const res = await authenticatedFetch('/backend/api/tips-api/tip-edit-api.php', {
      method: 'PUT',
      body: JSON.stringify(editTipData.value)
    })
    const data = await res.json()
    if (data.success) {
      const index = tips.value.findIndex(t => t.id === editTipData.value.id)
      if (index !== -1) tips.value[index] = { ...editTipData.value }
      bootstrap.Modal.getInstance(document.getElementById('editTipModal')).hide()
      showAlert('Tip updated successfully!')
    } else alert(data.message)
  } catch (err) {
    alert('Network error: ' + err.message)
  }
}

const deleteTip = async (id) => {
  if (!confirm('Are you sure you want to delete this tip?')) return
  try {
    const res = await authenticatedFetch('/backend/api/tips-api/tip-delete-api.php', {
      method: 'DELETE',
      body: JSON.stringify({ id })
    })
    const data = await res.json()
    if (data.success) {
      tips.value = tips.value.filter(t => t.id !== id)
      showAlert('Tip deleted successfully!')
    } else alert(data.message)
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
          <button class="nav-link mb-2 text-start" id="tips-tab" data-bs-toggle="tab" data-bs-target="#tips"
            type="button" role="tab">💡 Tips</button>
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
                          <button class="btn btn-sm btn-outline-info me-1"
                            @click="openUserDataModal(user)">Edit Data</button>
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

          <!-- TIPS -->
          <div class="tab-pane fade" id="tips" role="tabpanel">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <h5 class="card-title mb-3">Financial Tips</h5>
                
                <!-- Add New Tip Section -->
                <div class="card mb-3 bg-light">
                  <div class="card-body">
                    <h6 class="card-subtitle mb-3">Add New Tip</h6>
                    <div class="mb-2">
                      <input v-model="newTip.title" type="text" class="form-control" placeholder="Tip title" maxlength="255">
                    </div>
                    <div class="mb-2">
                      <textarea v-model="newTip.content" class="form-control" placeholder="Tip content" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" @click="createTip">Add Tip</button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table align-middle table-hover">
                    <thead class="table-primary">
                      <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="tip in tips" :key="tip.id">
                        <td>{{ tip.id }}</td>
                        <td>{{ tip.title }}</td>
                        <td>{{ tip.content.substring(0, 50) }}{{ tip.content.length > 50 ? '...' : '' }}</td>
                        <td>{{ new Date(tip.created_at).toLocaleDateString() }}</td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1"
                            @click="openEditTipModal(tip)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger"
                            @click="deleteTip(tip.id)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <p v-if="tipError" class="text-danger mt-2">{{ tipError }}</p>
                </div>
                <div v-if="alertVisible" class="alert alert-success" role="alert">
                  {{ alertMessage }}
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>

    <!-- User Data Modal -->
    <div class="modal fade" id="userDataModal" tabindex="-1" aria-labelledby="userDataModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="userDataModalLabel">Manage User Data</h5>
              <p class="mb-0 text-muted small">{{ selectedUserForData ? selectedUserForData.username : '' }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="btn-group w-100 mb-3">
              <button type="button" class="btn" :class="dataModalTab === 'transactions' ? 'btn-primary' : 'btn-outline-primary'"
                @click="dataModalTab = 'transactions'">
                Transactions
              </button>
              <button type="button" class="btn" :class="dataModalTab === 'categories' ? 'btn-primary' : 'btn-outline-primary'"
                @click="dataModalTab = 'categories'">
                Categories
              </button>
            </div>

            <div v-if="dataModalTab === 'transactions'">
              <div v-if="transactionLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
              </div>
              <div v-else>
                <div class="card border-0 shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                      <h6 class="mb-0">Add Transaction</h6>
                      <span class="text-muted small">Amounts shown in {{ selectedUserCurrency }} ({{ selectedUserSymbol }})</span>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-3">
                        <label class="form-label small text-muted">Amount</label>
                        <input type="number" min="0" step="0.01" class="form-control" v-model="newTransactionForm.amount"
                          :placeholder="`Amount in ${selectedUserSymbol}`" />
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small text-muted">Date</label>
                        <input type="date" class="form-control" v-model="newTransactionForm.date" />
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small text-muted">Type</label>
                        <select class="form-select" v-model="newTransactionForm.type">
                          <option value="income">Income</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small text-muted">Category</label>
                        <select class="form-select" v-model="newTransactionForm.category_id">
                          <option disabled value="">Select category</option>
                          <option v-for="category in userCategoryOptions" :key="category.id" :value="category.id">
                            {{ category.name }} ({{ category.type }})
                          </option>
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label small text-muted">Note</label>
                        <textarea class="form-control" rows="2" v-model="newTransactionForm.note" placeholder="Optional note"></textarea>
                      </div>
                    </div>
                    <div class="text-end mt-3">
                      <button class="btn btn-primary" @click="createTransactionForUser">Add Transaction</button>
                    </div>
                  </div>
                </div>
                <div v-if="transactionError" class="alert alert-danger">{{ transactionError }}</div>
                <div v-if="userTransactions.length" class="table-responsive">
                  <table class="table table-striped align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount ({{ selectedUserSymbol }} / {{ selectedUserCurrency }})</th>
                        <th>Note</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="transaction in userTransactions" :key="transaction.id">
                        <td>{{ transaction.id }}</td>
                        <td>{{ transaction.date ? new Date(transaction.date).toLocaleDateString() : '—' }}</td>
                        <td>
                          <span :class="transaction.type === 'income' ? 'badge bg-success' : 'badge bg-danger'">
                            {{ transaction.type }}
                          </span>
                        </td>
                        <td>{{ transaction.category_name || '—' }}</td>
                        <td>{{ formatTransactionAmount(transaction.amount) }}</td>
                        <td>{{ transaction.note || '—' }}</td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-2" @click="startEditingTransaction(transaction)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger" @click="deleteUserTransaction(transaction)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-else class="text-center text-muted py-3">No transactions found for this user.</p>

                <div v-if="transactionForm.id" class="border rounded bg-light p-3 mt-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Edit Transaction</h6>
                    <button class="btn btn-sm btn-link text-decoration-none" @click="cancelTransactionEdit">Cancel</button>
                  </div>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label small text-muted">Amount ({{ selectedUserSymbol }} / {{ selectedUserCurrency }})</label>
                      <input type="number" min="0" step="0.01" class="form-control" v-model="transactionForm.amount" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small text-muted">Date</label>
                      <input type="date" class="form-control" v-model="transactionForm.date" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small text-muted">Type</label>
                      <select class="form-select" v-model="transactionForm.type">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Category</label>
                      <select class="form-select" v-model="transactionForm.category_id">
                        <option disabled value="">Select category</option>
                        <option v-for="category in userCategoryOptions" :key="category.id" :value="category.id">
                          {{ category.name }} ({{ category.type }})
                        </option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Note</label>
                      <textarea class="form-control" rows="1" v-model="transactionForm.note" placeholder="Optional note"></textarea>
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button class="btn btn-secondary me-2" @click="cancelTransactionEdit">Cancel</button>
                    <button class="btn btn-primary" @click="submitTransactionUpdate">Save changes</button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else>
              <div v-if="categoryLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
              </div>
              <div v-else>
                <div class="card border-0 shadow-sm mb-3">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                      <h6 class="mb-0">Add Category</h6>
                      <span class="text-muted small">Custom categories only</span>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-8">
                        <label class="form-label small text-muted">Name</label>
                        <input type="text" class="form-control" v-model="newCategoryForm.name" placeholder="Category name" />
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small text-muted">Type</label>
                        <select class="form-select" v-model="newCategoryForm.type">
                          <option value="income">Income</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                    </div>
                    <div class="text-end mt-3">
                      <button class="btn btn-primary" @click="createCategoryForUser">Add Category</button>
                    </div>
                  </div>
                </div>
                <div v-if="categoryManagementError" class="alert alert-danger">{{ categoryManagementError }}</div>
                <div v-if="userCategories.length" class="table-responsive">
                  <table class="table table-striped align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="category in userCategories" :key="category.id">
                        <td>{{ category.id }}</td>
                        <td>{{ category.name }}</td>
                        <td>
                          <span :class="category.type === 'income' ? 'badge bg-success' : 'badge bg-danger'">
                            {{ category.type }}
                          </span>
                        </td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-2" @click="startEditingCategory(category)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger" @click="deleteUserCategory(category)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-else class="text-center text-muted py-3">This user has not created custom categories yet.</p>

                <div v-if="categoryForm.id" class="border rounded bg-light p-3 mt-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Edit Category</h6>
                    <button class="btn btn-sm btn-link text-decoration-none" @click="cancelCategoryEdit">Cancel</button>
                  </div>
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="form-label small text-muted">Name</label>
                      <input type="text" class="form-control" v-model="categoryForm.name" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small text-muted">Type</label>
                      <select class="form-select" v-model="categoryForm.type">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                      </select>
                    </div>
                  </div>
                  <div class="text-end mt-3">
                    <button class="btn btn-secondary me-2" @click="cancelCategoryEdit">Cancel</button>
                    <button class="btn btn-primary" @click="submitCategoryUpdate">Save changes</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
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

    <!-- Edit Tip Modal -->
    <div class="modal fade" id="editTipModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Tip</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input v-model="editTipData.title" class="form-control mb-2" placeholder="Tip title" maxlength="255" />
            <textarea v-model="editTipData.content" class="form-control" placeholder="Tip content" rows="5"></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="updateTip">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>