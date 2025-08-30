<template>
  <div>
    <h2 v-if="!id">Create Prize Tier</h2>
    <h2 v-else>Edit Prize Tier</h2>
    <form @submit.prevent="save">
      <div class="mb-3"><label class="form-label">Label</label><input v-model="form.label" class="form-control"/></div>
      <div class="mb-3"><label class="form-label">Amount (minor)</label><input v-model.number="form.amount_minor" class="form-control"/></div>
      <div class="mb-3"><label class="form-label">Weight</label><input v-model.number="form.weight" class="form-control"/></div>
      <button class="btn btn-primary">Save</button>
      <router-link to="/admin/prize-tiers" class="btn btn-link">Cancel</router-link>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default { props:['id'], data(){ return { form: { label:'', amount_minor:0, weight:1 } } }, async mounted(){ if(this.id){ const res=await axios.get(`/admin/prize-tiers/${this.id}`); this.form=res.data } }, methods:{ async save(){ if(this.id) await axios.put(`/admin/prize-tiers/${this.id}`, this.form); else await axios.post('/admin/prize-tiers', this.form); this.$router.push('/admin/prize-tiers') } } };
</script>
