<template>
  <div class="lottery-card-container">
    <!-- National Lottery Style Card -->
    <div class="lottery-card">
      <!-- Header Section -->
      <div class="lottery-header">
        <div class="lottery-logo">
          <div class="logo-circles">
            <div class="circle red"></div>
            <div class="circle blue"></div>
            <div class="circle green"></div>
            <div class="circle purple"></div>
            <div class="circle orange"></div>
          </div>
          <span class="logo-text">SCRATCH</span>
        </div>
        <div class="card-title">INSTANT WIN</div>
        <div class="card-serial">No. {{ ticket?.play_id?.slice(-8).toUpperCase() || 'XXXXXXXX' }}</div>
      </div>

      <!-- Game Instructions -->
      <div class="game-instructions">
        <h3>HOW TO PLAY</h3>
        <p>Scratch to reveal 3 symbols. Match 3 identical amounts to WIN that prize!</p>
      </div>

      <!-- Scratch Area -->
      <div class="scratch-area">
        <div v-if="revealed" class="result-area">
          <!-- Winner Display -->
          <div v-if="playSession?.outcome === 'WIN'" class="winner-section">
            <div class="win-banner">
              <span class="win-text">CONGRATULATIONS!</span>
              <div class="win-amount">{{ prettyAmount(playSession?.payout_minor || ticket?.prize?.amount_minor) }}</div>
              <div class="win-message">YOU'VE WON!</div>
            </div>
            <div class="winning-symbols">
              <div class="symbol-label">YOUR WINNING SYMBOLS:</div>
              <div class="symbols-row">
                <div v-for="(symbol, i) in symbols" :key="i" class="winning-symbol">{{ symbol }}</div>
              </div>
            </div>
          </div>
          
          <!-- Loser Display -->
          <div v-else class="loser-section">
            <div class="lose-banner">
              <span class="lose-text">BETTER LUCK NEXT TIME!</span>
              <div class="lose-message">No winning combination</div>
            </div>
            <div class="losing-symbols">
              <div class="symbol-label">YOUR SYMBOLS:</div>
              <div class="symbols-row">
                <div v-for="(symbol, i) in symbols" :key="i" class="losing-symbol">{{ symbol }}</div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="game-area">
          <div class="game-title">SCRATCH HERE</div>
          <div class="scratch-boxes">
            <ScratchBox v-for="(s,i) in symbols" :key="i" :box-index="i" :symbol="s" :initial-scratch-pct="initialScratch[i]" :revealed="revealedBoxes[i]" @progress="onBoxProgress" @revealed="onBoxRevealed" />
          </div>
        </div>
      </div>

      <!-- Progress Section -->
      <div class="progress-section">
        <div class="progress-label">PROGRESS: {{ Math.round(displayedProgress) }}%</div>
        <div class="progress-track">
          <div class="progress-fill" :style="{ width: displayedProgress + '%' }"></div>
        </div>
      </div>

      <!-- Revealing Message -->
      <div v-if="overallProgress >= 65 && !revealed" class="revealing-message">
        <div class="spinner"></div>
        <span>REVEALING RESULT...</span>
      </div>

      <!-- Action Buttons -->
      <div v-if="revealed" class="action-section">
        <button class="play-again-btn" @click="$emit('new-game')">
          PLAY AGAIN
        </button>
      </div>

      <!-- Footer -->
      <div class="lottery-footer">
        <div class="footer-text">Licensed game • Play responsibly • 18+</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
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
    // Keep per-box current scratch % separate from the revealed boolean flags
    const progressByBox = ref([0,0,0]);
    const revealedBoxes = ref([false,false,false]);
    const overallProgress = ref(0);
    // displayedProgress is a smooth animated value that follows overallProgress
    const displayedProgress = ref(0);
    const revealed = computed(() => !!(props.playSession?.revealed_at || props.playSession?.status === 'REVEALED'));
    const winningSymbol = ref('');

    function prettyAmount(a){
      if (!a) return '£0.00';
      if (typeof a === 'object' && a.amount_minor) return `£${(a.amount_minor/100).toFixed(2)}`;
      if (typeof a === 'number') return `£${(a/100).toFixed(2)}`;
      return a.label || '£0.00';
    }

    function setupFromSession(){
      // If server provided explicit box symbols, use them
      if (props.playSession?.box_symbols && Array.isArray(props.playSession.box_symbols) && props.playSession.box_symbols.length === 3) {
        symbols.value = [...props.playSession.box_symbols];
        winningSymbol.value = props.playSession.winning_symbol || '';
        initialScratch.value = [0,0,0];
        progressByBox.value = [0,0,0];
        revealedBoxes.value = [false,false,false];
        return;
      }
      
      // If this is a winning play session and we have a payout amount, show that amount in all three boxes
      if (props.playSession?.outcome === 'WIN' && (props.playSession.payout_minor || props.playSession.payout_minor === 0)) {
        const amt = prettyAmount(props.playSession.payout_minor);
        symbols.value = [amt, amt, amt];
        winningSymbol.value = amt;
        initialScratch.value = [0,0,0];
        progressByBox.value = [0,0,0];
        revealedBoxes.value = [false,false,false];
        return;
      }
 
      // If no box_symbols yet and not a known winning amount, show placeholder amounts (this happens before server generates them)
      const pool = ['£1','£5','£10','£20','£50','£100','£500'];
      
      // Don't show any specific pattern until we have the real amounts from the server
      // Just show random placeholder amounts
      const placeholders = [];
      for (let i = 0; i < 3; i++) {
        placeholders.push(pool[Math.floor(Math.random() * pool.length)]);
      }
      symbols.value = placeholders;
      winningSymbol.value = '';
      initialScratch.value = [0,0,0];
      progressByBox.value = [0,0,0];
      revealedBoxes.value = [false,false,false];
    }

    watch(() => props.playSession?.play_id, () => setupFromSession(), { immediate: true });

    function onBoxProgress(payload){
      progressByBox.value[payload.boxIndex] = payload.scratchPct;
      overallProgress.value = Math.round((Number(progressByBox.value[0]||0) + Number(progressByBox.value[1]||0) + Number(progressByBox.value[2]||0)) / 3);
      emit('scratch-progress', overallProgress.value);
    }

    function onBoxRevealed(payload){
      revealedBoxes.value[payload.boxIndex] = true;
      emit('reveal', payload);
    }

    // animate displayedProgress toward overallProgress for a nicer UX
    let _raf = null;
    function animateTo(target) {
      if (_raf) { cancelAnimationFrame(_raf); _raf = null; }
      const start = Number(displayedProgress.value || 0);
      const duration = 380; // ms
      const t0 = performance.now();
      function step(now) {
        const t = Math.min(1, (now - t0) / duration);
        // ease out quadratic
        const eased = 1 - Math.pow(1 - t, 2);
        displayedProgress.value = start + (target - start) * eased;
        if (t < 1) _raf = requestAnimationFrame(step); else _raf = null;
      }
      _raf = requestAnimationFrame(step);
    }

    watch(overallProgress, (v) => {
      animateTo(Number(v || 0));
    }, { immediate: true });

    // clean up RAF when component unmounts
    onBeforeUnmount(() => {
      if (_raf) cancelAnimationFrame(_raf);
    });

    // return displayedProgress so template uses the animated value
    return { symbols, initialScratch, revealedBoxes, overallProgress, displayedProgress, onBoxProgress, onBoxRevealed, revealed, prettyAmount, winningSymbol };
  }
}
</script>

<style scoped>
/* National Lottery Modern Design */
.lottery-card-container {
  max-width: 420px;
  margin: 0 auto;
  padding: 16px;
  background: linear-gradient(135deg, #e6f3ff 0%, #f0f8ff 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lottery-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.1),
    0 8px 16px rgba(0, 0, 0, 0.08);
  border: 3px solid #0066cc;
  width: 100%;
  max-width: 380px;
}

/* Header */
.lottery-header {
  background: linear-gradient(135deg, #0066cc 0%, #004499 100%);
  color: white;
  padding: 20px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.lottery-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="30" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
  pointer-events: none;
}

.lottery-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-bottom: 12px;
  position: relative;
  z-index: 1;
}

.logo-circles {
  display: flex;
  gap: 4px;
}

.circle {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  animation: logoFloat 3s ease-in-out infinite;
}

.circle.red { background: #ff4444; animation-delay: 0s; }
.circle.blue { background: #4488ff; animation-delay: 0.2s; }
.circle.green { background: #44ff44; animation-delay: 0.4s; }
.circle.purple { background: #aa44ff; animation-delay: 0.6s; }
.circle.orange { background: #ff8844; animation-delay: 0.8s; }

@keyframes logoFloat {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-3px); }
}

.logo-text {
  font-size: 24px;
  font-weight: 900;
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.card-title {
  font-size: 20px;
  font-weight: 800;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.card-serial {
  font-size: 14px;
  opacity: 0.9;
  font-weight: 600;
}

/* Game Instructions */
.game-instructions {
  background: #f8f9fa;
  padding: 16px 20px;
  border-bottom: 2px solid #e9ecef;
}

.game-instructions h3 {
  color: #0066cc;
  font-size: 16px;
  font-weight: 800;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.game-instructions p {
  color: #495057;
  font-size: 14px;
  margin: 0;
  font-weight: 600;
  line-height: 1.4;
}

/* Game Area */
.scratch-area {
  padding: 24px 20px;
}

.game-title {
  text-align: center;
  font-size: 18px;
  font-weight: 800;
  color: #0066cc;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.scratch-boxes {
  display: grid;
  /* Use flexible columns that can shrink to fit the card */
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 20px;
  /* small horizontal padding so boxes don't touch the card edge */
  padding: 0 6px;
  align-items: stretch;
}

/* Result Areas */
.result-area {
  text-align: center;
}

.winner-section {
  animation: winnerEnter 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.win-banner {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  padding: 24px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
  position: relative;
  overflow: hidden;
}

.win-banner::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  animation: shine 2s ease-in-out infinite;
}

@keyframes shine {
  0% { left: -100%; }
  50% { left: 100%; }
  100% { left: -100%; }
}

.win-text {
  display: block;
  font-size: 16px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.win-amount {
  font-size: 36px;
  font-weight: 900;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
  margin-bottom: 8px;
}

.win-message {
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.loser-section {
  animation: loserEnter 0.6s ease-out;
}

.lose-banner {
  background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.lose-text {
  display: block;
  font-size: 16px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.lose-message {
  font-size: 14px;
  opacity: 0.9;
}

/* Symbols Display */
.winning-symbols, .losing-symbols {
  margin-top: 16px;
}

.symbol-label {
  font-size: 12px;
  font-weight: 700;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
}

.symbols-row {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.winning-symbol, .losing-symbol {
  background: white;
  border: 3px solid #28a745;
  color: #28a745;
  font-size: 18px;
  font-weight: 900;
  padding: 12px 16px;
  border-radius: 8px;
  min-width: 60px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  animation: symbolPulse 2s infinite;
}

.losing-symbol {
  border-color: #6c757d;
  color: #6c757d;
  animation: none;
}

@keyframes symbolPulse {
  0%, 100% { 
    transform: scale(1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  50% { 
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
  }
}

/* Progress Section */
.progress-section {
  background: #f8f9fa;
  padding: 16px 20px;
  border-top: 2px solid #e9ecef;
}

.progress-label {
  display: block;
  font-size: 14px;
  font-weight: 700;
  color: #495057;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-track {
  background: #e9ecef;
  height: 12px;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive scratch box fixes: prevent overflow when the child ScratchBox sets a large min-width */
.scratch-boxes {
  display: grid;
  /* Use flexible columns that can shrink to fit the card */
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 20px;
  /* small horizontal padding so boxes don't touch the card edge */
  padding: 0 6px;
  align-items: stretch;
}

/* Use deep selector so we override styles inside the child component (scoped SFC) */
:deep(.scratch-box) {
  /* Allow the box to shrink below the component's internal min-width */
  min-width: 0 !important;
  min-height: 72px;
  height: 100%;
  box-sizing: border-box;
}

/* Ensure the canvas inside the box fills its parent bounds */
:deep(.scratch-box) .scratch-canvas {
  width: 100% !important;
  height: 100% !important;
  display: block;
}

@media (max-width: 480px) {
  .scratch-boxes {
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 0 4px;
  }
  :deep(.scratch-box) {
    min-height: 60px;
  }
}

/* Revealing Message */
.revealing-message {
  text-align: center;
  padding: 20px;
  color: #0066cc;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  font-size: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #e9ecef;
  border-top: 3px solid #0066cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Action Section */
.action-section {
  padding: 20px;
  text-align: center;
  border-top: 2px solid #e9ecef;
}

.play-again-btn {
  background: linear-gradient(135deg, #0066cc 0%, #004499 100%);
  color: white;
  border: none;
  padding: 16px 32px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 16px rgba(0, 102, 204, 0.3);
}

.play-again-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 102, 204, 0.4);
}

.play-again-btn:active {
  transform: translateY(0);
}

/* Footer */
.lottery-footer {
  background: #e9ecef;
  padding: 12px 20px;
  text-align: center;
}

.footer-text {
  font-size: 11px;
  color: #6c757d;
  font-weight: 600;
}

/* Animations */
@keyframes winnerEnter {
  0% {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes loserEnter {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Mobile Responsive */
@media (max-width: 480px) {
  .lottery-card-container {
    padding: 8px;
  }
  
  .lottery-card {
    max-width: 100%;
  }
  
  .scratch-boxes {
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 0 4px;
  }
  
  .win-amount {
    font-size: 28px;
  }
  
  .symbols-row {
    gap: 8px;
  }
  
  .winning-symbol, .losing-symbol {
    font-size: 16px;
    padding: 10px 12px;
    min-width: 50px;
  }
}
</style>
