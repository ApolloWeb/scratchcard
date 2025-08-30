<template>
  <div>
    <h3>Generation Batches</h3>

    <div class="card mb-3">
      <div class="card-body">
        <form @submit.prevent="create">
          <div class="row g-2">
            <div class="col-md-3">
              <input v-model="form.requested_count" type="number" class="form-control" placeholder="Requested count" required />
            </div>
            <div class="col-md-2">
              <input v-model.number="form.win_numerator" type="number" class="form-control" placeholder="Win numerator" required />
            </div>
            <div class="col-md-2">
              <input v-model.number="form.win_denominator" type="number" class="form-control" placeholder="Win denominator" required />
            </div>
            <div class="col-md-2">
              <input v-model.number="form.code_length" type="number" class="form-control" placeholder="Code length" />
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary">Create Batch</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <table class="table table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Requested</th>
          <th>Created</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in batches" :key="b.id">
          <td>{{ b.id }}</td>
          <td>{{ b.name }}</td>
          <td>{{ b.requested_count }}</td>
          <td>{{ b.created_count }}</td>
          <td>{{ b.status }}</td>
          <td><router-link :to="`/admin/generation-batches/${b.id}`" class="btn btn-sm btn-outline-primary">View</router-link></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
const batches = ref([]);
const form = ref({ requested_count: 1000, win_numerator: 1, win_denominator: 100, code_length: 8 });

async function load() {
  const res = await window.axios.get('/admin/generation-batches');
  batches.value = res.data;
}

async function create() {
  await window.axios.post('/admin/generation-batches', form.value);
  await load();
}

onMounted(load);
</script>
