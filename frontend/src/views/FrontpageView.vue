<template>
  <div>
   

    <main class="bg-light">
      <!-- Hero Section -->
      <section class="bg-white py-5">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h1 class="display-5 fw-bold mb-3 text-primary">Manage your money with clarity.</h1>
              <p class="lead text-secondary mb-4">
                Track expenses, plan budgets, and visualize your financial goals — all in one place.
              </p>
              <div class="mb-4">
                <router-link to="/user-registration" class="btn btn-primary btn-lg me-2">Get Started</router-link>
                <a class="btn btn-outline-secondary btn-lg" href="#features">Learn More</a>
              </div>
              <div>
                <span class="badge bg-success me-2">Secure</span>
                <span class="badge bg-info text-dark">Easy to use</span>
                <span class="badge bg-warning text-dark">Free Plan</span>
              </div>
            </div>
            <div class="col-lg-6 text-center">
              <div class="card border-0 shadow-sm">
                <div class="card-body">
                  <h5 class="card-title text-primary">Dashboard Preview</h5>
                  <div class="row g-2">
                    <div class="col-6">
                      <div class="p-3 border rounded bg-light-subtle">
                        <div class="fs-6 text-secondary">Income</div>
                        <div class="h4 text-success">$3,200</div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="p-3 border rounded bg-light-subtle">
                        <div class="fs-6 text-secondary">Expenses</div>
                        <div class="h4 text-danger">$1,450</div>
                      </div>
                    </div>
                  </div>
                  <div class="mt-3">
                    <svg width="100%" height="120" viewBox="0 0 600 120" preserveAspectRatio="none">
                      <polyline points="0,90 100,60 200,70 300,40 400,50 500,20 600,30"
                                fill="none" stroke="#0d6efd" stroke-width="6" stroke-linecap="round"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- About -->
      <section id="about" class="py-5 bg-light">
        <div class="container text-center">
          <h2 class="fw-bold text-primary mb-3">About PFT</h2>
          <p class="lead text-secondary mb-4">
            Personal Financial Tracker helps individuals and small teams understand their finances better through simple insights and smart analytics.
          </p>
          <div class="row g-4">
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="display-6 text-primary mb-3">💡</div>
                  <h5 class="fw-semibold">Smart Insights</h5>
                  <p class="text-secondary small">Get automatic analysis of your income, expenses, and habits — all visualized in one dashboard.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="display-6 text-success mb-3">🔒</div>
                  <h5 class="fw-semibold">Secure by Design</h5>
                  <p class="text-secondary small">Your data is protected with the latest encryption technologies and never shared with third parties.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="display-6 text-warning mb-3">⚡</div>
                  <h5 class="fw-semibold">Fast & Intuitive</h5>
                  <p class="text-secondary small">No clutter, no confusion. Add transactions, manage budgets, and view reports instantly.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section id="features" class="py-5 bg-white">
        <div class="container text-center">
          <h2 class="fw-bold text-primary mb-4">Key Features</h2>
          <div class="row g-4">
            <div class="col-md-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="fs-1 text-primary mb-2">📊</div>
                  <h6 class="fw-semibold">Expense Charts</h6>
                  <p class="small text-secondary">Visualize where your money goes each month.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="fs-1 text-success mb-2">🧾</div>
                  <h6 class="fw-semibold">Transaction History</h6>
                  <p class="small text-secondary">View and filter all transactions easily.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="fs-1 text-warning mb-2">💬</div>
                  <h6 class="fw-semibold">Smart Alerts</h6>
                  <p class="small text-secondary">Get notified when you exceed budget limits.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="fs-1 text-danger mb-2">🌐</div>
                  <h6 class="fw-semibold">Cloud Sync</h6>
                  <p class="small text-secondary">Access your data anywhere, on any device.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      
    </main>
  </div>
</template>

<script setup>
// No component logic required for static frontpage at the moment.
</script>

<style scoped>
/* Minor local adjustments and fallbacks */
.bg-light-subtle { background-color: #f8fafc; }
.card .card-body svg { display: block; }
</style>
