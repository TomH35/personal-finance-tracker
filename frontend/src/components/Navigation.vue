<template>
  <nav class="navbar navbar-expand bg-primary navbar-dark">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <span class="me-2" aria-hidden="true">🪙</span>
        <span class="fw-semibold">PFT</span>
      </a>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <RouterLink class="nav-link" to="/">Home</RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" to="/login">Login</RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
// Bootstrap-only UI; routing handled by <RouterLink>
</script>
