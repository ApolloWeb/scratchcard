<template>
  <div>
    <h3>Play Sessions</h3>
    <div class="mb-3">
      <input v-model="filter.batch_id" class="form-control" placeholder="Filter by batch id" />
    </div>
    <table class="table table-sm">
      <thead>
        <tr>
          <th>Code</th>
          <th>Status</th>
          <th>Prize</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in sessions.data || []" :key="p.id">
          <td>{{ p.code }}</td>
          <td>{{ p.status }}</td>
          <td>{{ p.payout_minor }}</td>
          <td>{{ new Date(p.created_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>

    <div class="d-flex justify-content-between">
      <button class="btn btn-secondary" @click="prev" :disabled="!sessions.prev_page_url">Previous</button>
      <button class="btn btn-secondary" @click="next" :disabled="!sessions.next_page_url">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
const filter = ref({ batch_id: '' });
const sessions = ref({ data: [] });

async function load(url) {
  const params = {};
  if (filter.value.batch_id) params.batch_id = filter.value.batch_id;
  const res = await window.axios.get('/admin/play-sessions', { params });
  sessions.value = res.data;
}

function prev() {
  if (sessions.value.prev_page_url) load(sessions.value.prev_page_url);
}
function next() {
  if (sessions.value.next_page_url) load(sessions.value.next_page_url);
}

watch(filter, () => load(), { deep: true });
onMounted(() => load());
</script>
