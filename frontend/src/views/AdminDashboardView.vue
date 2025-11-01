<script setup>
import { ref, onMounted } from 'vue';

const users = ref([]);
const error = ref('');

onMounted(async () => {
  try {
    const response = await fetch('/backend/api/user-api/user-get-all-api.php', {
      method: 'GET',
      credentials: 'include' // Important: sends session cookie
    });

    const data = await response.json();

    if (data.success) {
      users.value = data.users;
    } else {
      error.value = data.message || 'Failed to fetch users';
    }

  } catch (err) {
    error.value = 'Network error: ' + err.message;
  }
});

// Delete a user
const deleteUser = async (user_id) => {
  if (!confirm('Are you sure you want to delete this user?')) return;

  try {
    const res = await fetch('/backend/api/user-api/user-delete-api.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id })
    });
    const data = await res.json();
    if (data.success) {
      users.value = users.value.filter(u => u.user_id !== user_id);
      alert('User deleted successfully');
    } else {
      alert(data.message || 'Failed to delete user');
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  }
};

// Edit a user
const editUser = async (user) => {
  const newUsername = prompt('Enter new username', user.username);
  if (!newUsername) return;

  const newEmail = prompt('Enter new email', user.email);
  if (!newEmail) return;

  const newRole = prompt('Enter role (admin/user)', user.role);
  if (!['admin', 'user'].includes(newRole)) {
    alert('Invalid role');
    return;
  }

  try {
    const res = await fetch('/backend/api/user-api/user-edit-api.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: user.user_id,
        username: newUsername,
        email: newEmail,
        role: newRole
      })
    });
    const data = await res.json();
    if (data.success) {
      // Update local users array
      const index = users.value.findIndex(u => u.user_id === user.user_id);
      if (index !== -1) {
        users.value[index] = { ...users.value[index], username: newUsername, email: newEmail, role: newRole };
      }
      alert('User updated successfully');
    } else {
      alert(data.message || 'Failed to update user');
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  }
};
</script>

<template>
  <div class="container-fluid">
    <div class="row">

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-white border-end min-vh-100 p-3">
        <h5 class="text-primary mb-4">Dashboard</h5>
        <div class="nav flex-column nav-pills" id="adminTab" role="tablist" aria-orientation="vertical">
          <button class="nav-link active mb-2 text-start" id="users-tab" data-bs-toggle="tab" data-bs-target="#users"
            type="button" role="tab">
            👥 Users
          </button>
          <button class="nav-link mb-2 text-start" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories"
            type="button" role="tab">
            🗂️ Categories
          </button>
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
                  <button class="btn btn-sm btn-primary">+ Add User</button>
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
                          <button class="btn btn-sm btn-outline-warning me-1" @click="editUser(user)">Edit</button>
                          <button class="btn btn-sm btn-outline-danger"
                            @click="deleteUser(user.user_id)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- CATEGORIES -->
          <!-- TODO -->
          <div class="tab-pane fade" id="categories" role="tabpanel">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="card-title mb-0">Categories</h5>
                  <div class="input-group" style="max-width: 300px;">
                    <input type="text" class="form-control" placeholder="New category">
                    <button class="btn btn-primary">Add</button>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table align-middle table-hover">
                    <thead class="table-primary">
                      <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td>Groceries</td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1">Edit</button>
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </td>
                      </tr>
                      <tr>
                        <td>2</td>
                        <td>Utilities</td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1">Edit</button>
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>Entertainment</td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-warning me-1">Edit</button>
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
