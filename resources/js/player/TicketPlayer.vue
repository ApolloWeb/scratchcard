<template>
  <div class="player-root">
    <div class="game-container">
      <ScratchCard v-if="!error && ticket && playSession" :ticket="playSession" :play-session="playSession" @scratch-progress="onScratchProgress" @reveal="onReveal" @new-game="newGame" />

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
        console.log('Loaded ticket data:', data);
        ticket.value = data;
        playSession.value = data.play_session || null;
        console.log('Play session:', playSession.value);
      } catch (e) {
        console.error('Failed to load ticket', e);
        error.value = (e && e.message) ? e.message : 'Unable to load ticket';
      } finally {
        loading.value = false;
      }
    }

    async function onScratchProgress(pct){
      if (!playSession.value || playSession.value.revealed_at) return;
      
      console.log('Scratch progress:', pct, 'Play session play_id:', playSession.value.play_id);
      
      // Check if we have a valid play_id
      if (!playSession.value.play_id) {
        console.error('No play_id found in play session:', playSession.value);
        return;
      }
      
      // Update scratch progress on server
      try {
        const updated = await Api.updateScratchProgress(playSession.value.play_id, { 
          scratch_pct: Math.round(pct),
          client_seed: Date.now().toString() // Simple client seed
        });
        
        if (updated) {
          // Update the play session with new data from server
          playSession.value = {
            ...playSession.value,
            ...updated,
            // Ensure we keep the box_symbols if they come from server
            box_symbols: updated.box_symbols || playSession.value.box_symbols
          };
        }
        
        // Trigger full ticket reveal when overall progress reaches 65%
        if (Math.round(pct) >= 65 && !playSession.value.revealed_at) {
          try {
            const verified = await Api.verifyResult(playSession.value.play_id);
            if (verified) {
              playSession.value = {
                ...playSession.value, 
                ...verified,
                status: 'REVEALED',
                revealed_at: new Date().toISOString()
              };
            }
          } catch (e) { 
            console.error('Error verifying result:', e); 
          }
        }
      } catch (e) { 
        console.error('Error updating scratch progress:', e); 
      }
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
