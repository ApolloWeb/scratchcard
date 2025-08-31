<template>
  <div class="scratch-card-container">
    <div class="card-header">
      <div class="card-serial">#{{ ticket?.play_id?.slice(-8).toUpperCase() || 'XXXXXXXX' }}</div>
    </div>

    <div class="scratch-area">
      <div v-if="revealed" class="result-content">
        <!-- Winner Popover -->
        <div v-if="playSession?.outcome === 'WIN'" class="result-popover winner-popover">
          <div class="popover-icon">🎉</div>
          <div class="popover-title">Congratulations!</div>
          <div class="popover-amount">{{ prettyAmount(playSession?.payout_minor || ticket?.prize?.amount_minor) }}</div>
          <div class="popover-subtitle">3 {{ winningSymbol }} Match!</div>
          <div class="symbols-display">
            <span v-for="(symbol, i) in symbols" :key="i" class="symbol winning-symbol">{{ symbol }}</span>
          </div>
        </div>
        
        <!-- Loser Popover -->
        <div v-else class="result-popover loser-popover">
          <div class="popover-icon">😔</div>
          <div class="popover-title">Better luck next time!</div>
          <div class="popover-subtitle">Keep trying for the big win</div>
          <div class="symbols-display">
            <span v-for="(symbol, i) in symbols" :key="i" class="symbol">{{ symbol }}</span>
          </div>
        </div>
      </div>

      <div v-else class="box-grid">
        <ScratchBox v-for="(s,i) in symbols" :key="i" :box-index="i" :symbol="s" :initial-scratch-pct="initialScratch[i]" :revealed="revealedBoxes[i]" @progress="onBoxProgress" @revealed="onBoxRevealed" />
      </div>
    </div>

    <div class="progress-section">
      <div class="progress-info">Scratch Progress <strong>{{ Math.round(overallProgress) }}%</strong></div>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: overallProgress + '%' }"></div>
      </div>
    </div>

    <div v-if="overallProgress >= 65 && !revealed" class="reveal-message">
      <div class="spinner-border spinner-border-sm me-2" role="status"></div>
      Revealing prize...
    </div>

    <div class="action-buttons" v-if="revealed">
      <button class="btn btn-primary btn-lg" @click="$emit('new-game')">
        <i class="bi bi-arrow-clockwise me-2"></i>Play Again
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue';
import confetti from 'canvas-confetti';
import ScratchBox from './ScratchBox.vue';

export default {
  name: 'ScratchCard',
  components: { ScratchBox },
  props: {
    ticket: { type: Object, required: true },
    playSession: { type: Object, required: true }
  },
  emits: ['scratch-progress','reveal','new-game'],
  setup(props, { emit }){
    const symbols = ref(['','','']);
    const initialScratch = ref([0,0,0]);
    const revealedBoxes = ref([false,false,false]);
    const boxScratchProgress = ref([0,0,0]); // Track scratch progress for each box
    const overallProgress = ref(0);
    const revealed = computed(() => !!(props.playSession?.revealed_at || props.playSession?.status === 'REVEALED'));
    const winningSymbol = ref('');

    function prettyAmount(a){
      if (!a) return '£0.00';
      if (typeof a === 'number') return `£${(a/100).toFixed(2)}`;
      if (typeof a === 'object' && a.amount_minor) return `£${(a.amount_minor/100).toFixed(2)}`;
      if (typeof a === 'object' && a.label) return a.label;
      return '£0.00';
    }

    function setupFromSession(){
      // Use actual symbols from the play session if available
      if (props.playSession?.box_symbols && Array.isArray(props.playSession.box_symbols) && props.playSession.box_symbols.length === 3) {
        symbols.value = [...props.playSession.box_symbols];
        winningSymbol.value = props.playSession.winning_symbol || '';
        initialScratch.value = [0,0,0];
        revealedBoxes.value = [false,false,false];
        boxScratchProgress.value = [0,0,0];
        return;
      }
      
      // If no box_symbols yet, try to generate correct symbols based on ticket data
      // Check for winning amount in multiple places: revealed prize or play session payout
      let winAmountMinor = null;
      if (props.ticket?.prize?.amount_minor) {
        winAmountMinor = props.ticket.prize.amount_minor;
      } else if (props.playSession?.payout_minor && props.playSession?.payout_minor > 0) {
        winAmountMinor = props.playSession.payout_minor;
      }
      
      if (winAmountMinor) {
        // For winning tickets, show the actual prize amount in all 3 boxes
        const winAmount = '£' + Math.round(winAmountMinor / 100);
        symbols.value = [winAmount, winAmount, winAmount];
        winningSymbol.value = winAmount;
        initialScratch.value = [0,0,0];
        revealedBoxes.value = [false,false,false];
        boxScratchProgress.value = [0,0,0];
        return;
      }
      
      // For losing tickets or tickets without payout data, show placeholder amounts
      const pool = ['£1','£5','£10','£20','£50','£100','£500'];
      
      // Don't show any specific pattern until we have the real amounts from the server
      // Just show random placeholder amounts (ensuring no 3 match for losing tickets)
      const placeholders = [];
      for (let i = 0; i < 3; i++) {
        let amount;
        do {
          amount = pool[Math.floor(Math.random() * pool.length)];
        } while (i > 0 && placeholders.includes(amount)); // Ensure no duplicates for losing tickets
        placeholders.push(amount);
      }
      symbols.value = placeholders;
      winningSymbol.value = '';
      initialScratch.value = [0,0,0];
      revealedBoxes.value = [false,false,false];
      boxScratchProgress.value = [0,0,0];
    }

    watch(() => props.playSession?.play_id, () => setupFromSession(), { immediate: true });

    // Watch for when a win is revealed and trigger confetti
    watch(() => [revealed.value, props.playSession?.outcome], ([isRevealed, outcome]) => {
      if (isRevealed && outcome === 'WIN') {
        nextTick(() => {
          triggerWinConfetti();
        });
      }
    });

    function triggerWinConfetti() {
      // Burst of confetti from the center
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
      });
      
      // Side cannons
      setTimeout(() => {
        confetti({
          particleCount: 50,
          angle: 60,
          spread: 55,
          origin: { x: 0 }
        });
        confetti({
          particleCount: 50,
          angle: 120,
          spread: 55,
          origin: { x: 1 }
        });
      }, 200);
      
      // Golden confetti burst
      setTimeout(() => {
        confetti({
          particleCount: 30,
          spread: 60,
          colors: ['#FFD700', '#FFA500', '#FFFF00'],
          origin: { y: 0.7 }
        });
      }, 400);
    }

    function onBoxProgress(payload){
      boxScratchProgress.value[payload.boxIndex] = payload.scratchPct;
      overallProgress.value = Math.round((boxScratchProgress.value[0] + boxScratchProgress.value[1] + boxScratchProgress.value[2]) / 3);
      emit('scratch-progress', overallProgress.value);
    }

    function onBoxRevealed(payload){
      revealedBoxes.value[payload.boxIndex] = true;
      emit('reveal', payload);
    }

    return { symbols, initialScratch, revealedBoxes, boxScratchProgress, overallProgress, onBoxProgress, onBoxRevealed, revealed, prettyAmount, winningSymbol };
  }
}
</script>

<style scoped>
.scratch-card-container {
  max-width: 560px;
  margin: 0 auto;
  padding: 16px;
}

.card-serial {
  font-weight: 700;
  color: #444;
  font-size: 14px;
  text-align: center;
  margin-bottom: 16px;
}

.box-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.progress-section {
  margin: 20px 0;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 14px;
  color: #666;
}

.progress-bar {
  background: #eee;
  border-radius: 8px;
  height: 12px;
  overflow: hidden;
}

.progress-fill {
  background: linear-gradient(90deg, #60a5fa, #a78bfa);
  height: 100%;
  transition: width 0.3s ease;
}

.reveal-message {
  text-align: center;
  margin: 16px 0;
  color: #666;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
}

.result-content {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
  margin: 20px 0;
}

.result-popover {
  background: white;
  border-radius: 16px;
  padding: 32px 24px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  text-align: center;
  position: relative;
  animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border: 2px solid #f1f5f9;
  max-width: 400px;
  width: 100%;
}

.winner-popover {
  background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
  border-color: #f59e0b;
}

.loser-popover {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-color: #d1d5db;
}

.popover-icon {
  font-size: 48px;
  margin-bottom: 16px;
  animation: bounce 2s infinite;
}

.popover-title {
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 8px;
  color: #374151;
}

.winner-popover .popover-title {
  color: #92400e;
}

.popover-amount {
  font-size: 36px;
  font-weight: 900;
  color: #059669;
  margin: 16px 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.popover-subtitle {
  font-size: 16px;
  color: #6b7280;
  margin-bottom: 20px;
  font-weight: 500;
}

.winner-popover .popover-subtitle {
  color: #92400e;
}

.symbols-display {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 16px;
}

.symbol {
  font-size: 20px;
  font-weight: bold;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  color: #374151;
  min-width: 60px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.winning-symbol {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border-color: #047857;
  animation: pulse 2s infinite;
}

.action-buttons {
  text-align: center;
  margin-top: 24px;
}

.btn {
  border: none;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.btn-lg {
  padding: 12px 32px;
  font-size: 16px;
}

@keyframes popIn {
  0% {
    opacity: 0;
    transform: scale(0.3) translateY(50px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  60% {
    transform: translateY(-5px);
  }
}

@keyframes pulse {
  0% {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  70% {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 0 0 10px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(16, 185, 129, 0);
  }
}
</style>
