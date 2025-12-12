<template>
  <div>
    <main class="bg-light">
      <!-- Hero Section -->
      <section class="bg-white py-5">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h1 class="display-5 fw-bold mb-3" style="color: #1D2A5B;">Manage your money with clarity.</h1>
              <p class="lead text-secondary mb-4">
                Track expenses, plan budgets, and visualize your financial goals — all in one place.
              </p>
              <div class="mb-4">
                <router-link to="/user-registration" class="btn btn-lg me-2" style="background-color: #1D2A5B; color: white; border: none;">Get Started</router-link>
                <a class="btn btn-outline-secondary btn-lg" href="#features">Learn More</a>
              </div>
              <div class="d-flex gap-2">
                <span class="badge bg-success px-3 py-2">Secure</span>
                <span class="badge px-3 py-2" style="background-color: #1D2A5B;">Easy to use</span>
                <span class="badge bg-secondary px-3 py-2">Free Plan</span>
              </div>
            </div>
            <div class="col-lg-6 text-center">
              <div class="card border shadow-sm">
                <div class="card-body p-4">
                  <h5 class="card-title fw-bold mb-4" style="color: #1D2A5B;">Dashboard Preview</h5>
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
      <section id="about" class="py-5" style="background-color: #f8f9fa;">
        <div class="container text-center">
          <h2 class="fw-bold mb-3" style="color: #1D2A5B;">About PFT</h2>
          <p class="lead text-secondary mb-5">
            Personal Financial Tracker helps individuals and small teams understand their finances better through simple insights and smart analytics.
          </p>
          <div class="row g-4">
            <div class="col-md-4">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#1D2A5B" viewBox="0 0 16 16">
                      <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                  </div>
                  <h5 class="fw-semibold mb-3">Smart Insights</h5>
                  <p class="text-secondary small mb-0">Get automatic analysis of your income, expenses, and habits — all visualized in one dashboard.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4 text-center">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#2E8B57" viewBox="0 0 16 16" class="mx-auto">
                      <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    </svg>
                  </div>
                  <h5 class="fw-semibold mb-3">Secure by Design</h5>
                  <p class="text-secondary small mb-0">Your data is protected with the latest encryption technologies and never shared with third parties.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4 text-center">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#1D2A5B" viewBox="0 0 16 16" class="mx-auto">
                      <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                    </svg>
                  </div>
                  <h5 class="fw-semibold mb-3">Fast & Intuitive</h5>
                  <p class="text-secondary small mb-0">No clutter, no confusion. Add transactions, manage budgets, and view reports instantly.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section id="features" class="py-5 bg-white">
        <div class="container text-center">
          <h2 class="fw-bold mb-5" style="color: #1D2A5B;">Key Features</h2>
          <div class="row g-4">
            <div class="col-md-3">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#1D2A5B" viewBox="0 0 16 16">
                      <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                    </svg>
                  </div>
                  <h6 class="fw-semibold mb-2">Expense Charts</h6>
                  <p class="small text-secondary mb-0">Visualize where your money goes each month.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#2E8B57" viewBox="0 0 16 16">
                      <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                      <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                    </svg>
                  </div>
                  <h6 class="fw-semibold mb-2">Transaction History</h6>
                  <p class="small text-secondary mb-0">View and filter all transactions easily.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#1D2A5B" viewBox="0 0 16 16">
                      <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                    </svg>
                  </div>
                  <h6 class="fw-semibold mb-2">Smart Alerts</h6>
                  <p class="small text-secondary mb-0">Get notified when you exceed budget limits.</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#1D2A5B" viewBox="0 0 16 16">
                      <path d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/>
                      <path d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708l3 3z"/>
                    </svg>
                  </div>
                  <h6 class="fw-semibold mb-2">Cloud Sync</h6>
                  <p class="small text-secondary mb-0">Access your data anywhere, on any device.</p>
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
.card .card-body svg {
  display: block;
  margin-left: auto;
  margin-right: auto;
}
</style>
