<template>
  <div>
    <h2>Play Sessions</h2>
    <table class="table"><thead><tr><th>Code</th><th>Batch</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <tr v-for="s in sessions.data" :key="s.id">
          <td>{{ s.code }}</td>
          <td>{{ s.batch_id }}</td>
          <td>{{ s.status }}</td>
          <td><router-link :to="`/admin/play-sessions/${s.id}`" class="btn btn-sm btn-outline-secondary">View</router-link></td>
        </tr>
      </tbody></table>
  </div>
</template>

<script>
import axios from 'axios';
export default { data(){ return { sessions:{ data: [] } } }, mounted(){ this.fetch() }, methods:{ async fetch(){ const res = await axios.get('/admin/play-sessions'); this.sessions = res.data } } };
</script>
