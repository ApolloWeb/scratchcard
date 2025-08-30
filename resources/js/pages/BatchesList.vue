<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Generation Batches</h2>
      <router-link to="/admin/batches/create" class="btn btn-primary">Create</router-link>
    </div>
    <table class="table"><thead><tr><th>Name</th><th>Count</th><th></th></tr></thead>
      <tbody>
        <tr v-for="b in batches.data" :key="b.id">
          <td>{{ b.name }}</td>
          <td>{{ b.count }}</td>
          <td><router-link :to="`/admin/batches/${b.id}/edit`" class="btn btn-sm btn-outline-secondary">Edit</router-link></td>
        </tr>
      </tbody></table>
  </div>
</template>

<script>
import axios from 'axios';
export default { data(){ return { batches: { data: [] } } }, mounted(){ this.fetch() }, methods:{ async fetch(){ const res=await axios.get('/admin/batches'); this.batches=res.data } } };
</script>
