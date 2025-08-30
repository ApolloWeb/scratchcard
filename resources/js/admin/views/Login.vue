<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Admin Login</h5>
          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="form.email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input v-model="form.password" type="password" class="form-control" required />
            </div>
            <div class="mb-3 form-check">
              <input v-model="form.remember" type="checkbox" class="form-check-input" id="remember" />
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <div v-if="error" class="alert alert-danger">{{ error }}</div>
            <button class="btn btn-primary" type="submit">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = reactive({ email: '', password: '', remember: false });
const error = ref(null);

function submit() {
  error.value = null;
  window.axios.post('/admin/login', form).then(() => {
    window.location.href = '/admin';
  }).catch(err => {
    if (err.response && err.response.data && err.response.data.errors) {
      const errs = err.response.data.errors;
      error.value = Object.values(errs).flat().join(' ');
    } else {
      error.value = 'Login failed';
    }
  });
}
</script>
