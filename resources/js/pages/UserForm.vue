<template>
  <div>
    <h2 v-if="!id">Create User</h2>
    <h2 v-else>Edit User</h2>

    <form @submit.prevent="save">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input v-model="form.name" class="form-control" />
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input v-model="form.email" class="form-control" />
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input v-model="form.password" type="password" class="form-control" />
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select v-model="form.role" class="form-select">
          <option value="super_admin">super_admin</option>
          <option value="admin">admin</option>
          <option value="viewer">viewer</option>
        </select>
      </div>
      <div class="mb-3 form-check">
        <input v-model="form.is_active" type="checkbox" class="form-check-input" id="active" />
        <label class="form-check-label" for="active">Active</label>
      </div>

      <button class="btn btn-primary">Save</button>
      <router-link to="/admin/users" class="btn btn-link">Cancel</router-link>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  props: ['id'],
  data() { return { form: { name: '', email: '', password: '', role: 'viewer', is_active: true } }; },
  async mounted() { if (this.id) { const res = await axios.get(`/admin/users/${this.id}`); this.form = res.data; } },
  methods: {
    async save() {
      if (this.id) {
        await axios.put(`/admin/users/${this.id}`, this.form);
      } else {
        await axios.post('/admin/users', this.form);
      }
      this.$router.push('/admin/users');
    }
  }
};
</script>
