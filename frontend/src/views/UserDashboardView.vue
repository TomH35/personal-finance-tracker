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
                  Income <span class="badge bg-success">${{ totalIncome }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Expenses <span class="badge bg-danger">${{ totalExpenses }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  Balance <span class="badge bg-primary">${{ balance }}</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Categories</h6>
              <ul class="list-group list-group-flush mb-3">
                <li v-for="cat in categories" :key="cat" class="list-group-item small">
                  {{ cat }}
                </li>
              </ul>
              <div class="input-group input-group-sm">
                <input
                  type="text"
                  v-model="newCategory"
                  class="form-control"
                  placeholder="New category"
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

          <!-- Add Transaction -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Add Transaction</h6>
              <form @submit.prevent="addTransaction">
                <div class="row g-2">
                  <div class="col-md-4">
                    <input
                      type="text"
                      v-model="formData.desc"
                      class="form-control"
                      placeholder="Description"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <input
                      type="number"
                      v-model="formData.amount"
                      class="form-control"
                      placeholder="Amount"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <select v-model="formData.category" class="form-select" required>
                      <option value="">Select Category</option>
                      <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select v-model="formData.type" class="form-select" required>
                      <option value="income">Income</option>
                      <option value="expense">Expense</option>
                    </select>
                  </div>
                  <div class="col-12 mt-3">
                    <button class="btn btn-primary">Add Transaction</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Transactions -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Recent Transactions</h6>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-primary">
                    <tr>
                      <th>Date</th>
                      <th>Description</th>
                      <th>Category</th>
                      <th class="text-end">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(transaction, index) in transactions" :key="index">
                      <td>{{ transaction.date }}</td>
                      <td>{{ transaction.desc }}</td>
                      <td>{{ transaction.category }}</td>
                      <td
                        class="text-end"
                        :class="transaction.type === 'expense' ? 'text-danger' : 'text-success'"
                      >
                        {{ transaction.type === 'expense' ? '-' : '+' }}${{ transaction.amount }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Chart -->
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">Expenses by Category</h6>
              <canvas id="expensesChart" height="200"></canvas>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserDashboardView',
  data() {
    return {
      categories: [],
      transactions: [],
      newCategory: '',
      formData: {
        desc: '',
        amount: '',
        category: '',
        type: 'income',
      },
    }
  },
  computed: {
    totalIncome() {
      return this.transactions
        .filter((t) => t.type === 'income')
        .reduce((sum, t) => sum + t.amount, 0)
    },
    totalExpenses() {
      return this.transactions
        .filter((t) => t.type === 'expense')
        .reduce((sum, t) => sum + t.amount, 0)
    },
    balance() {
      return this.totalIncome - this.totalExpenses
    },
  },
  methods: {
    addCategory() {
      const name = this.newCategory.trim()
      if (name && !this.categories.includes(name)) {
        this.categories.push(name)
        this.newCategory = ''
      }
    },
    addTransaction() {
      const transaction = {
        date: new Date().toISOString().split('T')[0],
        desc: this.formData.desc,
        category: this.formData.category,
        amount: parseFloat(this.formData.amount),
        type: this.formData.type,
      }
      this.transactions.unshift(transaction)
      this.formData = {
        desc: '',
        amount: '',
        category: '',
        type: 'income',
      }
    },
  },
}
</script>
