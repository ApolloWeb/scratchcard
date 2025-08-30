<template>
  <div>
    <h3>Prize Tiers</h3>
    <div class="mb-3 d-flex">
      <input v-model="newTier.label" class="form-control me-2" placeholder="Label" />
      <input v-model.number="newTier.amount_minor" type="number" class="form-control me-2" placeholder="Amount (minor)" />
      <input v-model.number="newTier.weight" type="number" class="form-control me-2" placeholder="Weight" />
      <button class="btn btn-primary" @click="create">Add</button>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Label</th>
          <th>Amount</th>
          <th>Weight</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in tiers" :key="t.id">
          <td>{{ t.label }}</td>
          <td>{{ t.amount_minor }}</td>
          <td>{{ t.weight }}</td>
          <td><button class="btn btn-sm btn-danger" @click="remove(t.id)">Delete</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
const tiers = ref([]);
const newTier = ref({ label: '', amount_minor: 0, weight: 0 });

async function load() {
  const res = await window.axios.get('/admin/prize-tiers');
  tiers.value = res.data;
}

async function create() {
  await window.axios.post('/admin/prize-tiers', newTier.value);
  newTier.value = { label: '', amount_minor: 0, weight: 0 };
  await load();
}

async function remove(id) {
  if (!confirm('Delete?')) return;
  await window.axios.delete(`/admin/prize-tiers/${id}`);
  await load();
}

onMounted(load);
</script>
