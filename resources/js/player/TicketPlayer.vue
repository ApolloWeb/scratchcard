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
        console.log('Loaded ticket data:', data);
        // API returns top-level ticket information and a `play_session` object
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

    // load immediately on mount
    onMounted(() => {
      load();
    });

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
        console.log('Scratch update response', updated);

        // If API returned a wrapped play_session (TicketController style)
        if (updated.play_session) {
          playSession.value = updated.play_session;
          // ticket payload might include prize info at top-level depending on endpoint
          ticket.value = updated || ticket.value;
        } else {
          // PlayController scratch response may return flat fields - merge into existing playSession
          playSession.value = Object.assign({}, playSession.value, updated);
        }
      } catch (e) {
        console.error('Failed to update scratch progress', e);
      }
    }

    function onReveal(payload) {
      // payload may include the final play session data or outcome; refresh to be safe
      if (payload && payload.play_session) {
        playSession.value = payload.play_session;
      } else if (payload && payload.id) {
        playSession.value = Object.assign({}, playSession.value, payload);
      }

      // ensure ticket/prize info is accurate after reveal
      // attempt to re-fetch the ticket to get full prize details
      load();
    }

    function newGame() {
      // simple behaviour: reload ticket data (server will create a new play session if appropriate)
      load();
    }

    // expose to template
    return { ticket, playSession, loading, error, onScratchProgress, onReveal, newGame };
  }
}
</script>

<style scoped>
.game-container{padding:32}
.loading-plate{text-align:center}
</style>
