<template>
  <div>
    <h2 v-if="!id">Create Game Setting</h2>
    <h2 v-else>Edit Game Setting</h2>
    <form @submit.prevent="save">
      <div class="mb-3"><label class="form-label">Win Numerator</label><input v-model.number="form.win_numerator" class="form-control"/></div>
      <div class="mb-3"><label class="form-label">Win Denominator</label><input v-model.number="form.win_denominator" class="form-control"/></div>
      <button class="btn btn-primary">Save</button>
      <router-link to="/admin/game-settings" class="btn btn-link">Cancel</router-link>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default { props:['id'], data(){ return { form:{ win_numerator:1, win_denominator:10 } } }, async mounted(){ if(this.id){ const res=await axios.get(`/admin/game-settings/${this.id}`); this.form=res.data } }, methods:{ async save(){ if(this.id) await axios.put(`/admin/game-settings/${this.id}`, this.form); else await axios.post('/admin/game-settings', this.form); this.$router.push('/admin/game-settings') } } };
</script>
