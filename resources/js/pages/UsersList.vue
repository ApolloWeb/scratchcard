<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Admin Users</h2>
      <router-link to="/admin/users/create" class="btn btn-primary">Create</router-link>
    </div>

    <table class="table table-striped">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th></th></tr>
      </thead>
      <tbody>
        <tr v-for="user in users.data" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role }}</td>
          <td>{{ user.is_active ? 'Yes' : 'No' }}</td>
          <td>
            <router-link :to="`/admin/users/${user.id}/edit`" class="btn btn-sm btn-outline-secondary me-2">Edit</router-link>
            <button class="btn btn-sm btn-danger" @click="remove(user.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <nav v-if="users.meta">
      <ul class="pagination">
        <li class="page-item" :class="{disabled: !users.prev_page_url}">
          <button class="page-link" @click="fetch(users.prev_page_url)" :disabled="!users.prev_page_url">Prev</button>
        </li>
        <li class="page-item" :class="{disabled: !users.next_page_url}">
          <button class="page-link" @click="fetch(users.next_page_url)" :disabled="!users.next_page_url">Next</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return { users: { data: [] } };
  },
  mounted() {
    this.fetch('/admin/users');
  },
  methods: {
    async fetch(url) {
      const res = await axios.get(url || '/admin/users');
      this.users = res.data;
    },
    async remove(id) {
      if (!confirm('Delete user?')) return;
      await axios.delete(`/admin/users/${id}`);
      this.fetch();
    }
  }
};
</script>
