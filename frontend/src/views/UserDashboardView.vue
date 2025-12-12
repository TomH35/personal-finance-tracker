<template>
  <div class="min-vh-100" style="background-color: #f8f9fa;">
    <div class="container-fluid p-0">
      <div class="row g-0">
        <!-- Sidebar -->
        <nav class="col-md-4 col-lg-3 d-md-block bg-white border-end min-vh-100 p-0">
          <div class="p-4">
          <h5 class="fw-bold mb-4" style="color: #1D2A5B;">Overview</h5>
          <div class="card border shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="fw-semibold mb-3 text-uppercase small text-muted">Summary</h6>
              <div class="mb-3">
                <label class="form-label small text-muted">Time Window</label>
                <select v-model="summaryTimeWindow" class="form-select form-select-sm mb-2">
                  <option value="year">Year</option>
                  <option value="current-month">Current Month</option>
                  <option value="custom">Custom</option>
                </select>
                <div v-if="summaryTimeWindow === 'custom'">
                  <label class="form-label small text-muted">Start Date</label>
                  <input v-model="summaryCustomStartDate" type="date" class="form-control form-control-sm mb-2" />
                  <label class="form-label small text-muted">End Date</label>
                  <input v-model="summaryCustomEndDate" type="date" class="form-control form-control-sm mb-2" />
                </div>
                <button @click="updateSummary" class="btn btn-sm w-100" style="background-color: #1D2A5B; color: white; border: none;">
                  <i class="bi bi-arrow-clockwise me-1"></i>Update Summary
                </button>
              </div>
              <ul class="list-unstyled mb-0">
                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <span class="text-secondary">Income</span>
                  <span class="fw-bold text-success">{{ currencySymbol }}{{ summaryIncome.toFixed(2) }}</span>
                </li>
                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <span class="text-secondary">Expenses</span>
                  <span class="fw-bold text-danger">{{ currencySymbol }}{{ summaryExpenses.toFixed(2) }}</span>
                </li>
                <li class="d-flex justify-content-between align-items-center py-2">
                  <span class="text-secondary">Balance</span>
                  <span class="fw-bold" :style="{ color: summaryBalance >= 0 ? '#1D2A5B' : '#ffc107' }">{{ currencySymbol }}{{ summaryBalance.toFixed(2) }}</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="card border shadow-sm">
            <div class="card-body p-4">
              <h6 class="fw-semibold mb-3 text-uppercase small text-muted">Categories</h6>
              <div class="mb-3" style="max-height: 400px; overflow-y: auto;">
                <div 
                  v-for="cat in categories" 
                  :key="cat.id" 
                  class="mb-2 p-3 rounded d-flex justify-content-between align-items-start" 
                  style="background-color: #f8f9fa; border-left: 3px solid #1D2A5B;"
                >
                  <div class="d-flex flex-column">
                    <span class="fw-medium text-dark mb-2">{{ cat.name }}</span>
                    <div v-if="!cat.is_predefined" class="d-flex gap-2">
                      <button 
                        class="btn btn-sm d-flex align-items-center gap-1 px-2 py-1" 
                        style="border: 1px solid #1D2A5B; color: #1D2A5B; background: white; font-size: 0.75rem;" 
                        @click="openEditCategoryModal(cat)"
                        title="Edit category"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                        </svg>
                        <span>Edit</span>
                      </button>
                      <button 
                        class="btn btn-sm btn-danger d-flex align-items-center gap-1 px-2 py-1" 
                        style="font-size: 0.75rem;" 
                        @click="openDeleteCategoryModal(cat)"
                        title="Delete category"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                          <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                        </svg>
                        <span>Delete</span>
                      </button>
                    </div>
                    <div v-else class="text-muted small fst-italic">
                      Default category
                    </div>
                  </div>
                  <span class="badge" :class="cat.type === 'income' ? 'bg-success' : 'bg-danger'" style="font-size: 0.7rem;">
                    {{ cat.type }}
                  </span>
                </div>
              </div>
              <button class="btn btn-sm w-100" style="background-color: #1D2A5B; color: white; border: none;" @click="openCreateCategoryModal">
                <i class="bi bi-plus-circle me-1"></i>New Category
              </button>
            </div>
          </div>
          </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-8 ms-sm-auto col-lg-9 p-4">
          <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Manage your finances and track your spending</p>
          </div>

          <!-- Financial Tips Spotlight -->
          <div v-if="tips.length" class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h5 class="fw-bold mb-1" style="color: #1D2A5B;">Financial Tips</h5>
              </div>
              <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark">{{ tips.length }} tips</span>
                <div class="d-flex gap-2">
                  <button 
                    @click="prevTip" 
                    class="btn btn-sm btn-outline-secondary rounded-circle"
                    style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                    :disabled="tips.length <= 2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                  </button>
                  <button 
                    @click="nextTip" 
                    class="btn btn-sm btn-outline-secondary rounded-circle"
                    style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                    :disabled="tips.length <= 2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div style="overflow: hidden; position: relative;">
              <div 
                style="display: flex; transition: transform 0.5s ease-in-out;"
                :style="{ transform: `translateX(-${currentTipIndex * (tips.length === 1 ? 100 : 50)}%)` }"
              >
                <div 
                  v-for="(tip, index) in tips" 
                  :key="tip.id"
                  :style="{ minWidth: tips.length === 1 ? '100%' : '50%', padding: '0 0.5rem' }"
                >
                  <div class="card border-0 shadow-sm text-white" :style="tipCardStyle(index)" style="height: 180px;">
                    <div class="card-body d-flex flex-column p-3">
                      <div class="d-flex justify-content-between align-items-center mb-2 opacity-75 small">
                        <span>Tip {{ index + 1 }}</span>
                        <i class="bi bi-lightning-charge-fill"></i>
                      </div>
                      <h6 class="fw-bold mb-2">{{ tip.title }}</h6>
                      <p class="flex-grow-1 mb-2 small" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">{{ tip.content }}</p>
                      <div class="d-flex justify-content-between align-items-center small opacity-75">
                        <span style="font-size: 0.75rem;">{{ tip.created_at ? new Date(tip.created_at).toLocaleDateString() : 'Just added' }}</span>
                        <span class="fw-semibold" style="font-size: 0.75rem;">Stay inspired</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-center mt-3" v-if="tips.length > 2">
              <div class="d-inline-flex gap-2">
                <button 
                  v-for="i in Math.ceil(tips.length / 2)" 
                  :key="i"
                  @click="currentTipIndex = (i - 1) * 2"
                  class="btn btn-sm rounded-circle"
                  :class="currentTipIndex >= (i - 1) * 2 && currentTipIndex < i * 2 ? 'btn-primary' : 'btn-outline-secondary'"
                  style="width: 10px; height: 10px; padding: 0; border-width: 2px;"
                ></button>
              </div>
            </div>
          </div>

          <!-- Financial Overview Chart -->
          <div class="card border shadow-sm mb-4">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0" style="color: #1D2A5B;">Financial Overview</h5>
                <select v-model="selectedChartType" class="form-select form-select-sm" style="width: auto;">
                  <option value="monthly-comparison">Monthly Income vs Expenses</option>
                  <option value="expense-breakdown">Expense Breakdown by Category</option>
                  <option value="income-breakdown">Income Breakdown by Category</option>
                  <option value="balance-trend">Balance Trend Over Time</option>
                </select>
              </div>
              
              <!-- Category Filter for Expense Breakdown -->
              <div v-if="selectedChartType === 'expense-breakdown'" class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label small text-muted mb-0">Filter by Categories:</label>
                  <div class="btn-group btn-group-sm" role="group">
                    <button 
                      type="button"
                      class="btn"
                      :class="selectedExpenseCategories.length === expenseCategoryOptions.length ? 'btn-success' : 'btn-outline-success'"
                      @click="selectAllExpenseCategories"
                      :disabled="selectedExpenseCategories.length === expenseCategoryOptions.length"
                    >
                      <i class="bi bi-check-all me-1"></i>Select All
                    </button>
                    <button 
                      type="button"
                      class="btn"
                      :class="selectedExpenseCategories.length === 0 ? 'btn-danger' : 'btn-outline-danger'"
                      @click="deselectAllExpenseCategories"
                      :disabled="selectedExpenseCategories.length === 0"
                    >
                      <i class="bi bi-x-lg me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <button 
                    v-for="cat in expenseCategoryOptions" 
                    :key="cat"
                    type="button"
                    class="btn btn-sm"
                    :class="selectedExpenseCategories.includes(cat) ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="toggleExpenseCategory(cat)"
                  >
                    <i v-if="selectedExpenseCategories.includes(cat)" class="bi bi-check-circle-fill me-1"></i>
                    <i v-else class="bi bi-circle me-1"></i>
                    {{ cat }}
                  </button>
                </div>
              </div>
              
              <div style="height: 300px;">
                <Bar v-if="selectedChartType === 'monthly-comparison'" :data="monthlyComparisonData" :options="barChartOptions" />
                <Doughnut v-else-if="selectedChartType === 'expense-breakdown'" :data="expenseBreakdownData" :options="doughnutChartOptions" />
                <Doughnut v-else-if="selectedChartType === 'income-breakdown'" :data="incomeBreakdownData" :options="doughnutChartOptions" />
                <Line v-else-if="selectedChartType === 'balance-trend'" :data="balanceTrendData" :options="lineChartOptions" />
              </div>
              <div class="d-flex align-items-center">
                <div class="btn-group btn-group-sm" role="group" aria-label="Period selector">
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary" 
                    :class="{ 'active': selectedPeriod === 'week' }"
                    @click="selectedPeriod = 'week'"
                    title="Last 7 days"
                  >Week</button>
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary" 
                    :class="{ 'active': selectedPeriod === 'month' }"
                    @click="selectedPeriod = 'month'"
                    title="Last 30 days"
                  >Month</button>
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary" 
                    :class="{ 'active': selectedPeriod === 'year' }"
                    @click="selectedPeriod = 'year'"
                    title="Last 12 months"
                  >Year</button>
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary" 
                    :class="{ 'active': selectedPeriod === 'all-time' }"
                    @click="selectedPeriod = 'all-time'"
                    title="All-Time Data"
                  >All-Time</button>
                </div>
                <button
                  type="button" 
                  class="btn btn-primary ms-auto" 
                  @click="generatePDF"
                  title="Save the chart as PDF"
                  >Save as PDF</button>
              </div>
            </div>
          </div>

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
          <div class="card border shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-4" style="color: #1D2A5B;">Add Transaction</h5>
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
                    <button class="btn" style="background-color: #1D2A5B; color: white; border: none;" :disabled="loading">
                      <span v-if="loading">Adding...</span>
                      <span v-else>Add Transaction</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card border shadow-sm mb-4">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0" style="color: #1D2A5B;">Recent Transactions</h5>
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
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr class="border-bottom">
                      <th class="text-muted fw-semibold small text-uppercase">Type</th>
                      <th class="text-muted fw-semibold small text-uppercase">Date</th>
                      <th class="text-muted fw-semibold small text-uppercase">Category</th>
                      <th class="text-muted fw-semibold small text-uppercase">Note</th>
                      <th class="text-end text-muted fw-semibold small text-uppercase">Amount</th>
                      <th class="text-end text-muted fw-semibold small text-uppercase">Actions</th>
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
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { authenticatedFetch } from '@/utils/api'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement,
  Filler
} from 'chart.js'
import { jsPDF } from "jspdf";

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, PointElement, LineElement, Filler)

export default {
  name: 'UserDashboardView',
  components: {
    Bar,
    Doughnut,
    Line
  },
  setup() {
    const loginStore = useLoginStore()
    const notificationStore = useNotificationStore()
    const router = useRouter()

    const incomeList = ref([])
    const expenseList = ref([])
    const allTransactions = ref([])
    const categories = ref([])
    const tips = ref([])
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
    // Track which months have shown modals (format: "YYYY-MM-warning" or "YYYY-MM-critical")
    const shownModalsThisSession = ref(new Set())

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

    // Summary time window
    const summaryTimeWindow = ref('current-month')
    const summaryStartDate = ref('')
    const summaryEndDate = ref('')
    const summaryCustomStartDate = ref('')
    const summaryCustomEndDate = ref('')
    
    // Initialize custom dates with current month
    const initializeCustomDates = () => {
      const now = new Date()
      const year = now.getFullYear()
      const month = String(now.getMonth() + 1).padStart(2, '0')
      const lastDay = new Date(year, now.getMonth() + 1, 0).getDate()
      summaryCustomStartDate.value = `${year}-${month}-01`
      summaryCustomEndDate.value = `${year}-${month}-${lastDay}`
    }
    
    // Update summary based on selected time window
    function updateSummary() {
      const now = new Date()
      
      if (summaryTimeWindow.value === 'year') {
        summaryStartDate.value = `${now.getFullYear()}-01-01`
        summaryEndDate.value = `${now.getFullYear()}-12-31`
      } else if (summaryTimeWindow.value === 'current-month') {
        const year = now.getFullYear()
        const month = String(now.getMonth() + 1).padStart(2, '0')
        const lastDay = new Date(year, now.getMonth() + 1, 0).getDate()
        summaryStartDate.value = `${year}-${month}-01`
        summaryEndDate.value = `${year}-${month}-${lastDay}`
      } else if (summaryTimeWindow.value === 'custom') {
        if (summaryCustomStartDate.value && summaryCustomEndDate.value) {
          summaryStartDate.value = summaryCustomStartDate.value
          summaryEndDate.value = summaryCustomEndDate.value
        }
      }
    }
    
    // Initialize on mount
    watch(summaryTimeWindow, (newValue) => {
      if (newValue === 'custom' && !summaryCustomStartDate.value) {
        initializeCustomDates()
      }
    })
    
    // Set initial dates for current month
    onMounted(() => {
      updateSummary()
      initializeCustomDates()
    })

    // Chart type selector
    const selectedChartType = ref('monthly-comparison')

    // Period selector: week | month | year | all-time
    const selectedPeriod = ref('year') // default 'year'

    // Category filter for expense breakdown chart
    const selectedExpenseCategories = ref([])
    const currentTipIndex = ref(0)
    
    const tipGradientPalette = [
      'linear-gradient(135deg, #1D2A5B, #3D64A8)',
      'linear-gradient(135deg, #1E9E63, #4CD4A8)',
      'linear-gradient(135deg, #B85CFF, #6F3CD7)',
      'linear-gradient(135deg, #FF6A3A, #FF4F81)',
      'linear-gradient(135deg, #FFB347, #FFCC33)'
    ]
    
    const prevTip = () => {
      if (currentTipIndex.value > 0) {
        currentTipIndex.value -= 2
      } else {
        // Loop to end
        const maxIndex = Math.max(0, tips.value.length - 2)
        currentTipIndex.value = Math.floor(maxIndex / 2) * 2
      }
    }
    
    const nextTip = () => {
      if (currentTipIndex.value + 2 < tips.value.length) {
        currentTipIndex.value += 2
      } else {
        // Loop to start
        currentTipIndex.value = 0
      }
    }
    
    // Get unique expense category names from transactions
    const expenseCategoryOptions = computed(() => {
      const uniqueCategories = new Set()
      expenseList.value.forEach(transaction => {
        const categoryName = transaction.category_name || 'Uncategorized'
        uniqueCategories.add(categoryName)
      })
      return Array.from(uniqueCategories).sort()
    })
    
    // Initialize selectedExpenseCategories with all categories
    watch(expenseCategoryOptions, (newOptions) => {
      if (newOptions.length > 0 && selectedExpenseCategories.value.length === 0) {
        selectedExpenseCategories.value = [...newOptions]
      }
    }, { immediate: true })
    
    // Toggle individual category
    function toggleExpenseCategory(category) {
      const index = selectedExpenseCategories.value.indexOf(category)
      if (index > -1) {
        selectedExpenseCategories.value.splice(index, 1)
      } else {
        selectedExpenseCategories.value.push(category)
      }
    }
    
    // Select all categories
    function selectAllExpenseCategories() {
      selectedExpenseCategories.value = [...expenseCategoryOptions.value]
    }
    
    // Deselect all categories
    function deselectAllExpenseCategories() {
      selectedExpenseCategories.value = []
    }

    const tipCardStyle = (index) => ({
      background: tipGradientPalette[index % tipGradientPalette.length],
      color: '#fff'
    })

    // Returns a local date key in the form "YYYY-MM-DD"
    function dateKey(d) {
      return (
        d.getFullYear() +
        '-' +
        String(d.getMonth() + 1).padStart(2, '0') +
        '-' +
        String(d.getDate()).padStart(2, '0')
      )
    }

    // Date parsing from DB: supports "YYYY-MM-DD" (without time) and full ISO strings
    // If only "YYYY-MM-DD", creates a local Date(year, month-1, day) (midnight local)
    function parseDate(dateInput) {
      if (!dateInput) return null
      if (dateInput instanceof Date) return dateInput

      const s = String(dateInput).trim()
      const simpleDateMatch = /^\d{4}-\d{2}-\d{2}$/.test(s)
      if (simpleDateMatch) {
        const [y, m, d] = s.split('-').map(Number)
        return new Date(y, m - 1, d) // local midnight
      }
      return new Date(s)
    }

    const getPeriodStartDate = (period) => {
      if (period === 'all-time') return null // All-time has no limit
      const now = new Date()
      let days = 30
      if (period === 'week') days = 7
      else if (period === 'month') days = 30
      else if (period === 'year') days = 365

      const start = new Date(now.getFullYear(), now.getMonth(), now.getDate())
      start.setDate(start.getDate() - (days - 1))
      start.setHours(0, 0, 0, 0)
      return start
    }

    // Format day label 'Mon 12/12' or month 'Dec 2025'
    const formatDayLabel = (date) => {
      return date.toLocaleDateString('en-US', { weekday: 'short', month: 'numeric', day: 'numeric' })
    }
    const formatMonthLabel = (date) => {
      return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
    }

    // Color palette for category charts
    const categoryColors = [
      'rgba(54, 162, 235, 0.8)',
      'rgba(255, 99, 132, 0.8)',
      'rgba(255, 206, 86, 0.8)',
      'rgba(75, 192, 192, 0.8)',
      'rgba(153, 102, 255, 0.8)',
      'rgba(255, 159, 64, 0.8)',
      'rgba(199, 199, 199, 0.8)',
      'rgba(83, 102, 255, 0.8)',
      'rgba(255, 99, 255, 0.8)',
      'rgba(99, 255, 132, 0.8)'
    ]

    // 1. Monthly Income vs Expenses (Bar Chart) 
    const monthlyComparisonData = computed(() => {
      const period = selectedPeriod.value

      // week / month (daily)
      if (period === 'week' || period === 'month') {
        const startDate = getPeriodStartDate(period)
        const txInPeriod = allTransactions.value.filter(t => {
          const d = parseDate(t.date)
          if (!d) return false
          d.setHours(0,0,0,0)
          return d >= startDate
        })

        const dailyTotals = {}
        const now = new Date()
        const days = period === 'week' ? 7 : 30
        for (let i = days - 1; i >= 0; i--) {
          const d = new Date(now.getFullYear(), now.getMonth(), now.getDate())
          d.setDate(d.getDate() - i)
          d.setHours(0,0,0,0)
          const key = dateKey(d)
          dailyTotals[key] = { income: 0, expense: 0, date: new Date(d) }
        }

        txInPeriod.forEach(t => {
          const dt = parseDate(t.date)
          if (!dt) return
          dt.setHours(0,0,0,0)
          const key = dateKey(dt)
          if (!dailyTotals[key]) return
          const amount = convertCurrency(parseFloat(t.amount))
          if (t.type === 'income') dailyTotals[key].income += amount
          else dailyTotals[key].expense += amount
        })

        const keys = Object.keys(dailyTotals).sort()
        const labels = keys.map(k => formatDayLabel(dailyTotals[k].date))
        const incomeData = keys.map(k => dailyTotals[k].income)
        const expenseData = keys.map(k => dailyTotals[k].expense)

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Income', backgroundColor: 'rgba(40, 167, 69, 0.8)', borderColor: 'rgb(40, 167, 69)', borderWidth: 1, data: incomeData.length ? incomeData : [0] },
            { label: 'Expenses', backgroundColor: 'rgba(220, 53, 69, 0.8)', borderColor: 'rgb(220, 53, 69)', borderWidth: 1, data: expenseData.length ? expenseData : [0] }
          ]
        }
      } 
      // year (monthly aggregation)
      else if (period === 'year') {
        const startDate = getPeriodStartDate('year')
        const txInPeriod = allTransactions.value.filter(t => {
          const d = parseDate(t.date)
          if (!d) return false
          d.setHours(0,0,0,0)
          return d >= startDate
        })
        const monthlyData = {}

        txInPeriod.forEach(transaction => {
          const date = parseDate(transaction.date)
          const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
          if (!monthlyData[monthKey]) monthlyData[monthKey] = { income: 0, expense: 0 }
          const amount = convertCurrency(parseFloat(transaction.amount))
          if (transaction.type === 'income') monthlyData[monthKey].income += amount
          else monthlyData[monthKey].expense += amount
        })

        const sortedMonths = Object.keys(monthlyData).sort()
        const labels = sortedMonths.map(month => {
          const [y, m] = month.split('-')
          return formatMonthLabel(new Date(y, parseInt(m) - 1))
        })
        const incomeData = sortedMonths.map(m => monthlyData[m].income)
        const expenseData = sortedMonths.map(m => monthlyData[m].expense)

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Income', backgroundColor: 'rgba(40, 167, 69, 0.8)', borderColor: 'rgb(40, 167, 69)', borderWidth: 1, data: incomeData.length ? incomeData : [0] },
            { label: 'Expenses', backgroundColor: 'rgba(220, 53, 69, 0.8)', borderColor: 'rgb(220, 53, 69)', borderWidth: 1, data: expenseData.length ? expenseData : [0] }
          ]
        }
      }
      // all-time (yearly aggregation)
      else if (period === 'all-time') {
        const yearlyData = {}

        allTransactions.value.forEach(transaction => {
          const year = parseDate(transaction.date).getFullYear()
          if (!yearlyData[year]) yearlyData[year] = { income: 0, expense: 0 }
          const amount = convertCurrency(parseFloat(transaction.amount))
          if (transaction.type === 'income') yearlyData[year].income += amount
          else yearlyData[year].expense += amount
        })

        const sortedYears = Object.keys(yearlyData).sort((a,b) => a - b)
        const labels = sortedYears
        const incomeData = sortedYears.map(y => yearlyData[y].income)
        const expenseData = sortedYears.map(y => yearlyData[y].expense)

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Income', backgroundColor: 'rgba(40, 167, 69, 0.8)', borderColor: 'rgb(40, 167, 69)', borderWidth: 1, data: incomeData.length ? incomeData : [0] },
            { label: 'Expenses', backgroundColor: 'rgba(220, 53, 69, 0.8)', borderColor: 'rgb(220, 53, 69)', borderWidth: 1, data: expenseData.length ? expenseData : [0] }
          ]
        }
      }
    })

    // 2. Expense Breakdown by Category (Doughnut Chart)
    const expenseBreakdownData = computed(() => {
      const period = selectedPeriod.value
      const startDate = getPeriodStartDate(period)
      const categoryTotals = {}

      expenseList.value.forEach(transaction => {
        const d = parseDate(transaction.date)
        if (!d) return
        d.setHours(0,0,0,0)
        if (startDate && d < startDate) return // Only if it's not all-time
        const categoryName = transaction.category_name || 'Uncategorized'
        
        // Filter by selected categories
        if (!selectedExpenseCategories.value.includes(categoryName)) return
        
        const amount = convertCurrency(parseFloat(transaction.amount))
        categoryTotals[categoryName] = (categoryTotals[categoryName] || 0) + amount
      })

      const labels = Object.keys(categoryTotals)
      const data = Object.values(categoryTotals)

      return {
        labels: labels.length > 0 ? labels : ['No Expenses'],
        datasets: [{
          data: data.length > 0 ? data : [0],
          backgroundColor: categoryColors.slice(0, labels.length || 1),
          borderWidth: 2,
          borderColor: '#fff'
        }]
      }
    })

    // 3. Income Breakdown by Category (Doughnut Chart)
    const incomeBreakdownData = computed(() => {
      const period = selectedPeriod.value
      const startDate = getPeriodStartDate(period)
      const categoryTotals = {}

      incomeList.value.forEach(transaction => {
        const d = parseDate(transaction.date)
        if (!d) return
        d.setHours(0,0,0,0)
        if (startDate && d < startDate) return // Only if it's not all-time
        const categoryName = transaction.category_name || 'Uncategorized'
        const amount = convertCurrency(parseFloat(transaction.amount))
        categoryTotals[categoryName] = (categoryTotals[categoryName] || 0) + amount
      })

      const labels = Object.keys(categoryTotals)
      const data = Object.values(categoryTotals)

      return {
        labels: labels.length > 0 ? labels : ['No Expenses'],
        datasets: [{
          data: data.length > 0 ? data : [0],
          backgroundColor: categoryColors.slice(0, labels.length || 1),
          borderWidth: 2,
          borderColor: '#fff'
        }]
      }
    })

    // 4. Balance Trend Over Time (Line Chart)
    const balanceTrendData = computed(() => {
      const period = selectedPeriod.value

      // All-time does not require startDate, others do
      const startDate = period !== 'all-time' ? getPeriodStartDate(period) : null
      const txInPeriod = allTransactions.value.filter(t => {
        if (period === 'all-time') return true
        const d = parseDate(t.date); if (!d) return false; d.setHours(0,0,0,0)
        return d >= startDate
      })

      if (period === 'year') {
        // Monthly balance for last 12 months
        const monthly = {}
        txInPeriod.forEach(t => {
          const d = parseDate(t.date)
          if (!d) return
          const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`
          if (!monthly[key]) monthly[key] = { income: 0, expense: 0 }
          const amount = convertCurrency(parseFloat(t.amount))
          if (t.type === 'income') monthly[key].income += amount
          else monthly[key].expense += amount
        })

        const keys = Object.keys(monthly).sort()
        const labels = keys.map(k => {
          const [y, m] = k.split('-')
          return formatMonthLabel(new Date(y, parseInt(m)-1))
        })
        const balanceData = keys.map(k => monthly[k].income - monthly[k].expense)
        let cumulative = 0
        const cumulativeData = balanceData.map(b => { cumulative += b; return cumulative })

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Monthly Balance', data: balanceData.length ? balanceData : [0], borderColor: 'rgb(54, 162, 235)', backgroundColor: 'rgba(54, 162, 235, 0.1)', fill: true, tension: 0.3 },
            { label: 'Cumulative Balance', data: cumulativeData.length ? cumulativeData : [0], borderColor: 'rgb(75, 192, 192)', backgroundColor: 'rgba(75, 192, 192, 0.1)', fill: false, tension: 0.3, borderDash: [5,5] }
          ]
        }
      } else if (period === 'all-time') {
        // Yearly balance for all-time
        const yearly = {}
        txInPeriod.forEach(t => {
          const year = parseDate(t.date).getFullYear()
          if (!yearly[year]) yearly[year] = { income: 0, expense: 0 }
          const amount = convertCurrency(parseFloat(t.amount))
          if (t.type === 'income') yearly[year].income += amount
          else yearly[year].expense += amount
        })

        const sortedYears = Object.keys(yearly).sort((a, b) => a - b)
        const labels = sortedYears
        const balanceData = sortedYears.map(y => yearly[y].income - yearly[y].expense)
        let cumulative = 0
        const cumulativeData = balanceData.map(b => { cumulative += b; return cumulative })

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Yearly Balance', data: balanceData.length ? balanceData : [0], borderColor: 'rgb(54, 162, 235)', backgroundColor: 'rgba(54, 162, 235, 0.1)', fill: true, tension: 0.3 },
            { label: 'Cumulative Balance', data: cumulativeData.length ? cumulativeData : [0], borderColor: 'rgb(75, 192, 192)', backgroundColor: 'rgba(75, 192, 192, 0.1)', fill: false, tension: 0.3, borderDash: [5,5] }
          ]
        }
      } else {
        // Daily for week/month
        const now = new Date()
        const days = period === 'week' ? 7 : 30
        const daily = {}
        for (let i = days -1; i >= 0; i--) {
          const d = new Date(now.getFullYear(), now.getMonth(), now.getDate())
          d.setDate(d.getDate() - i)
          d.setHours(0,0,0,0)
          const key = dateKey(d)
          daily[key] = { income: 0, expense: 0, date: new Date(d) }
        }

        txInPeriod.forEach(t => {
          const dt = parseDate(t.date)
          if (!dt) return
          dt.setHours(0,0,0,0)
          const key = dateKey(dt)
          if (!daily[key]) return
          const amount = convertCurrency(parseFloat(t.amount))
          if (t.type === 'income') daily[key].income += amount
          else daily[key].expense += amount
        })

        const keys = Object.keys(daily).sort()
        const labels = keys.map(k => formatDayLabel(daily[k].date))
        const balanceData = keys.map(k => daily[k].income - daily[k].expense)
        let cumulative = 0
        const cumulativeData = balanceData.map(b => { cumulative += b; return cumulative })

        return {
          labels: labels.length > 0 ? labels : ['No Data'],
          datasets: [
            { label: 'Daily Balance', data: balanceData.length ? balanceData : [0], borderColor: 'rgb(54, 162, 235)', backgroundColor: 'rgba(54, 162, 235, 0.1)', fill: true, tension: 0.3 },
            { label: 'Cumulative Balance', data: cumulativeData.length ? cumulativeData : [0], borderColor: 'rgb(75, 192, 192)', backgroundColor: 'rgba(75, 192, 192, 0.1)', fill: false, tension: 0.3, borderDash: [5,5] }
          ]
        }
      }
    })

    // Chart Options
    const barChartOptions = computed(() => ({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Monthly Income vs Expenses' },
        tooltip: {
          callbacks: {
            label: (context) => `${context.dataset.label}: ${currencySymbol.value}${context.parsed.y.toFixed(2)}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: (value) => currencySymbol.value + value }
        }
      }
    }))

    const doughnutChartOptions = computed(() => ({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right' },
        title: { 
          display: true, 
          text: selectedChartType.value === 'expense-breakdown' 
            ? 'Expense Breakdown by Category' 
            : 'Income Breakdown by Category'
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const total = context.dataset.data.reduce((a, b) => a + b, 0)
              const percentage = ((context.parsed / total) * 100).toFixed(1)
              return `${context.label}: ${currencySymbol.value}${context.parsed.toFixed(2)} (${percentage}%)`
            }
          }
        }
      }
    }))

    const lineChartOptions = computed(() => ({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Balance Trend Over Time' },
        tooltip: {
          callbacks: {
            label: (context) => `${context.dataset.label}: ${currencySymbol.value}${context.parsed.y.toFixed(2)}`
          }
        }
      },
      scales: {
        y: {
          ticks: { callback: (value) => currencySymbol.value + value }
        }
      }
    }))
    
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

    const generatePDF = () => {
      const canvas = document.querySelector('canvas');

      if (!canvas) return;

      const imgData = canvas.toDataURL("image/png");

      const padding = 16;

      const pdf = new jsPDF({
        orientation: "landscape",
        unit: "px",
        format: [canvas.width + padding * 2, canvas.height + padding * 2 + 20],
      });

      pdf.text(`Financial Overview - ${selectedPeriod.value}`, padding, 20);
      pdf.addImage(imgData, "PNG", padding, padding + 20, canvas.width, canvas.height);
      pdf.save('chart.pdf');
    }
    
    const incomeCategories = computed(() => {
      return categories.value.filter(cat => cat.type === 'income')
    })

    const filteredCategories = computed(() => {
      return categories.value.filter(cat => cat.type === formData.value.type)
    })

    const editFilteredCategories = computed(() => {
      return categories.value.filter(cat => cat.type === editFormData.value.type)
    })
    
    // Summary calculations based on time window
    const summaryIncome = computed(() => {
      if (!summaryStartDate.value || !summaryEndDate.value) return 0
      
      const start = new Date(summaryStartDate.value)
      const end = new Date(summaryEndDate.value)
      end.setHours(23, 59, 59, 999)
      
      const sum = incomeList.value
        .filter(item => {
          const itemDate = parseDate(item.date)
          return itemDate && itemDate >= start && itemDate <= end
        })
        .reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      
      return convertCurrency(sum)
    })
    
    const summaryExpenses = computed(() => {
      if (!summaryStartDate.value || !summaryEndDate.value) return 0
      
      const start = new Date(summaryStartDate.value)
      const end = new Date(summaryEndDate.value)
      end.setHours(23, 59, 59, 999)
      
      const sum = expenseList.value
        .filter(item => {
          const itemDate = parseDate(item.date)
          return itemDate && itemDate >= start && itemDate <= end
        })
        .reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      
      return convertCurrency(sum)
    })
    
    const summaryBalance = computed(() => {
      return summaryIncome.value - summaryExpenses.value
    })

    // Calculate expenses for a specific month (given a date)
    function calculateMonthExpenses(dateString) {
      const targetDate = new Date(dateString)
      const targetMonth = targetDate.getMonth()
      const targetYear = targetDate.getFullYear()
      
      const monthExpenses = expenseList.value.filter(expense => {
        const expenseDate = new Date(expense.date)
        return expenseDate.getMonth() === targetMonth && 
               expenseDate.getFullYear() === targetYear
      })
      
      const total = monthExpenses.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0)
      console.log(`Expenses for ${targetYear}-${targetMonth + 1}:`, total, 'from', monthExpenses.length, 'transactions')
      return total
    }

    
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
        
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-get-all-api.php')
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
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-get-all-api.php')
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
        const response = await authenticatedFetch('/backend/api/category-api/category-get-all-api.php')
        const data = await response.json()
        if (data.success) {
          categories.value = data.categories || []
        }
      } catch (err) {
        console.error('Failed to load categories:', err)
      }
    }

    async function loadTips() {
      try {
        const response = await authenticatedFetch('/backend/api/tips-api/tip-get-all-api.php')
        const data = await response.json()
        if (data.success) {
          tips.value = data.tips || []
        }
      } catch (err) {
        console.error('Failed to load tips:', err)
      }
    }

    async function loadLimits() {
      try {
        const response = await authenticatedFetch('/backend/api/limit-api/get-limit-api.php')
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

    function checkLimits(monthlyExpenses, transactionDate = null, forceShowType = null) {
      console.log('Checking limits:', {
        monthlyExpenses,
        warningLimit: warningLimit.value,
        criticalLimit: criticalLimit.value,
        enabled: limitsEnabled.value,
        transactionDate,
        forceShowType
      })
      
      if (!limitsEnabled.value) {
        console.log('Limits are disabled')
        return
      }

      currentExpenseAmount.value = monthlyExpenses
      
      // Calculate month keys for tracking
      const yearMonth = transactionDate ? new Date(transactionDate).toISOString().slice(0, 7) : new Date().toISOString().slice(0, 7)
      const criticalKey = `${yearMonth}-critical`
      const warningKey = `${yearMonth}-warning`
      
      // If forceShowType is specified by backend, always show that modal
      // Backend only sets this when threshold is first crossed
      if (forceShowType === 'critical') {
        limitOverage.value = monthlyExpenses - criticalLimit.value
        const modal = new window.bootstrap.Modal(document.getElementById('limitCriticalModal'))
        modal.show()
        shownModalsThisSession.value.add(criticalKey)
        return
      } else if (forceShowType === 'warning') {
        limitOverage.value = monthlyExpenses - warningLimit.value
        const modal = new window.bootstrap.Modal(document.getElementById('limitWarningModal'))
        modal.show()
        shownModalsThisSession.value.add(warningKey)
        return
      }
      
      // Otherwise, use the old logic with per-month tracking for manual checks
      // (yearMonth, criticalKey, and warningKey already defined above)

      // Check critical first - if critical is exceeded, only show critical modal
      if (monthlyExpenses >= criticalLimit.value) {
        if (!shownModalsThisSession.value.has(criticalKey)) {
          limitOverage.value = monthlyExpenses - criticalLimit.value
          console.log('Critical limit reached! Over by:', limitOverage.value)
          shownModalsThisSession.value.add(criticalKey)
          
          // Show modal for immediate user feedback
          // Note: Backend creates persistent notifications automatically
          const modal = new window.bootstrap.Modal(document.getElementById('limitCriticalModal'))
          modal.show()
        }
      } else if (monthlyExpenses >= warningLimit.value) {
        if (!shownModalsThisSession.value.has(warningKey)) {
          limitOverage.value = monthlyExpenses - warningLimit.value
          console.log('Warning limit reached! Over by:', limitOverage.value)
          shownModalsThisSession.value.add(warningKey)
          
          // Show modal for immediate user feedback
          // Note: Backend creates persistent notifications automatically
          const modal = new window.bootstrap.Modal(document.getElementById('limitWarningModal'))
          modal.show()
        }
      } else {
        console.log('Within limits')
        // Remove flags for this month if user goes back under the limits
        shownModalsThisSession.value.delete(warningKey)
        shownModalsThisSession.value.delete(criticalKey)
      }
    }

    async function addTransaction() {
      loading.value = true
      try {
      	const rate = exchangeRates[userCurrency.value] || 1.0
    	const amountInUSD = parseFloat(formData.value.amount) / rate
    	
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-create-api.php', {
          method: 'POST',
          body: JSON.stringify({
            type: formData.value.type,
            amount: amountInUSD,
            category_id: formData.value.category_id,
            note: formData.value.note || null,
            date: formData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction added successfully!'
          const wasExpense = formData.value.type === 'expense'
          const transactionDate = formData.value.date
          formData.value = {
            type: 'income',
            amount: '',
            category_id: '',
            note: '',
            date: new Date().toISOString().split('T')[0]
          }
          await loadAllTransactions()
          
          // Reload notifications and optionally show popup
          if (wasExpense) {
            await notificationStore.loadNotifications()
            // Only show popup if backend says to (first time exceeding threshold this month)
            if (data.show_popup && data.notification_type) {
              const monthExpenses = calculateMonthExpenses(transactionDate)
              checkLimits(monthExpenses, transactionDate, data.notification_type)
            }
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
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-delete-api.php', {
          method: 'DELETE',
          body: JSON.stringify({ id, type })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction deleted successfully!'
          
          // Store transaction info before reloading
          const wasExpense = type === 'expense'
          
          await loadAllTransactions()
          
          // Reload notifications (no popup on delete)
          if (wasExpense) {
            await notificationStore.loadNotifications()
          }
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
      const rate = exchangeRates[userCurrency.value] || 1.0
      
      editFormData.value = {
        type: transaction.type,
        amount: parseFloat(transaction.amount * rate).toFixed(2),
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
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-delete-api.php', {
          method: 'DELETE',
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
      const rate = exchangeRates[userCurrency.value] || 1.0
      
      editFormData.value = {
        type: transaction.type || 'income',
        amount: parseFloat(transaction.amount * rate).toFixed(2),
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
      const rate = exchangeRates[userCurrency.value] || 1.0
	const amountInUSD = parseFloat(editFormData.value.amount) / rate
	
        const response = await authenticatedFetch('/backend/api/transaction-api/transaction-edit-api.php', {
          method: 'PUT',
          body: JSON.stringify({
            id: editingId.value,
            type: editFormData.value.type,
            amount: amountInUSD,
            category_id: editFormData.value.category_id,
            note: editFormData.value.note || null,
            date: editFormData.value.date
          })
        })
        const data = await response.json()

        if (data.success) {
          successMessage.value = 'Transaction updated successfully!'
          
          const wasExpense = editFormData.value.type === 'expense'
          const transactionDate = editFormData.value.date
          
          const modal = window.bootstrap.Modal.getInstance(document.getElementById('editIncomeModal'))
          modal.hide()
          editingId.value = null
          await loadAllTransactions()
          
          // Reload notifications and optionally show popup
          if (wasExpense) {
            await notificationStore.loadNotifications()
            // Only show popup if backend says to
            if (data.show_popup && data.notification_type) {
              const monthExpenses = calculateMonthExpenses(transactionDate)
              checkLimits(monthExpenses, transactionDate, data.notification_type)
            }
          }
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
        const response = await authenticatedFetch('/backend/api/custom-categories-api/custom-category-create-api.php', {
          method: 'POST',
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
        const response = await authenticatedFetch('/backend/api/custom-categories-api/custom-category-edit-api.php', {
          method: 'POST',
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
        const response = await authenticatedFetch('/backend/api/custom-categories-api/custom-category-delete-api.php', {
          method: 'POST',
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
        const res = await authenticatedFetch('/backend/api/user-api/user-get-profile-api.php')
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
      await loadTips()
      await loadAllTransactions()
      await loadLimits()
    })

    return {
      incomeList,
      expenseList,
      allTransactions,
      categories,
      tips,
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
      generatePDF,
      incomeCategories,
      filteredCategories,
      editFilteredCategories,
      filteredAndSortedTransactions,
      summaryTimeWindow,
      summaryStartDate,
      summaryEndDate,
      summaryCustomStartDate,
      summaryCustomEndDate,
      updateSummary,
      summaryIncome,
      summaryExpenses,
      summaryBalance,
      limits,
      warningLimit,
      criticalLimit,
      limitsEnabled,
      limitOverage,
      currentExpenseAmount,
      userCurrency,
      currencySymbol,
      convertCurrency,
      selectedChartType,
      selectedPeriod,
      selectedExpenseCategories,
      expenseCategoryOptions,
      toggleExpenseCategory,
      selectAllExpenseCategories,
      deselectAllExpenseCategories,
      monthlyComparisonData,
      expenseBreakdownData,
      incomeBreakdownData,
      balanceTrendData,
      barChartOptions,
      doughnutChartOptions,
      lineChartOptions,
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
      confirmDeleteCategory,
      currentTipIndex,
      prevTip,
      nextTip,
      tipCardStyle
    }
  }
}
</script>
