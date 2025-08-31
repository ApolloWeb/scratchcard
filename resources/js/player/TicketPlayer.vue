<template>
  <div class="player-root">
    <div class="game-container">
      <ScratchCard v-if="!error && ticket && playSession" :ticket="ticket" :play-session="playSession" @scratch-progress="onScratchProgress" @reveal="onReveal" @new-game="newGame" />

      <div v-else-if="loading" class="loading-plate">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        <p>Loading your scratch card...</p>
      </div>

      <div v-else class="loading-plate">
        <div class="alert alert-danger" role="alert">
          Failed to load ticket. {{ error || 'Please try again later.' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Api from './api';
import ScratchCard from './ScratchCard.vue';

export default {
  name: 'TicketPlayer',
  props: { code: { type: String, required: true } },
  components: { ScratchCard },
  setup(props) {
    const ticket = ref(null);
    const playSession = ref(null);
    const loading = ref(true);
    const error = ref('');

    async function load() {
      loading.value = true;
      error.value = '';
      try {
        const data = await Api.getTicket(props.code);
        ticket.value = data;
        playSession.value = data.play_session || null;
      } catch (e) {
        console.error('Failed to load ticket', e);
        error.value = (e && e.message) ? e.message : 'Unable to load ticket';
      } finally {
        loading.value = false;
      }
    }

    async function onScratchProgress(pct){
      if (!playSession.value || playSession.value.revealed_at) return;
      
      // Update scratch progress on server
      try {
        const updated = await Api.updateScratchProgress(playSession.value.play_id, { scratch_pct: Math.round(pct) });
        if (updated) playSession.value = {...playSession.value, ...updated};
        
        // Trigger full ticket reveal when overall progress reaches 65%
        if (Math.round(pct) >= 65 && !playSession.value.revealed_at) {
          try {
            const verified = await Api.verifyResult(playSession.value.play_id);
            if (verified) playSession.value = {...playSession.value, ...verified};
          } catch (e) { console.error('Error verifying result:', e); }
        }
      } catch (e) { console.error(e); }
    }

    async function onReveal(payload){
      // Individual box revealed - don't trigger full ticket reveal yet
      // The full ticket reveal should be triggered by overall progress, not individual box reveals
      console.log(`Box ${payload.boxIndex} revealed at ${payload.scratchPct}%`);
    }

    async function newGame(){ window.location.reload(); }

    onMounted(() => { load(); });

    return { ticket, playSession, onScratchProgress, onReveal, newGame, loading, error };
  }
}
</script>

<style scoped>
.game-container{min-height:260px;padding:32px}
.loading-plate{text-align:center}
</style>
