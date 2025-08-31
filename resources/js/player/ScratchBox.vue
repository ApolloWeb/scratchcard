<template>
  <div class="scratch-box" @mousemove="onMove" @mouseleave="onLeave">
    <div v-if="revealed" class="box-revealed"><div class="symbol">{{ symbol }}</div></div>
    <canvas ref="canvas" class="scratch-canvas"
      @mousedown.prevent="start"
      @mousemove.prevent="scratchMove"
      @mouseup.prevent="end"
      @touchstart.prevent="start"
      @touchmove.prevent="scratchMove"
      @touchend.prevent="end"
    ></canvas>
    <div class="scratch-cursor" :style="cursorStyle" v-show="showCursor"></div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';

export default {
  name: 'ScratchBox',
  props: {
    boxIndex: { type: Number, required: true },
    symbol: { type: String, default: '' },
    initialScratchPct: { type: Number, default: 0 },
    revealed: { type: Boolean, default: false },
    threshold: { type: Number, default: 65 },
    brushRatio: { type: Number, default: 0.18 }
  },
  emits: ['progress', 'revealed'],
  setup(props, { emit }) {
    const canvas = ref(null);
    const ctx = ref(null);
    const isDown = ref(false);
    const scratchPct = ref(props.initialScratchPct || 0);
    const localRevealed = ref(!!props.revealed);
    const showCursor = ref(false);
    const cursorX = ref(0);
    const cursorY = ref(0);

    function bounds() {
      const el = canvas.value;
      return el?.getBoundingClientRect() || { left: 0, top: 0, width: 0, height: 0 };
    }

    function resizeCanvas() {
      if (!canvas.value) return;
      const parent = canvas.value.parentElement.getBoundingClientRect();
      canvas.value.width = Math.max(160, Math.floor(parent.width));
      canvas.value.height = Math.max(80, Math.floor(parent.height));
      ctx.value = canvas.value.getContext('2d', { willReadFrequently: true });
      drawOverlay();
    }

    function drawOverlay() {
      if (!ctx.value || !canvas.value) return;
      const c = canvas.value;
      ctx.value.clearRect(0, 0, c.width, c.height);
      ctx.value.fillStyle = '#bdbdbd';
      ctx.value.fillRect(0,0,c.width,c.height);
      const g = ctx.value.createLinearGradient(0,0,c.width,c.height);
      g.addColorStop(0, 'rgba(220,220,220,0.95)');
      g.addColorStop(1, 'rgba(180,180,180,0.95)');
      ctx.value.fillStyle = g;
      ctx.value.fillRect(0,0,c.width,c.height);
      ctx.value.fillStyle = 'rgba(255,255,255,0.6)';
      ctx.value.font = '700 14px system-ui, Arial';
      ctx.value.textAlign = 'center';
      ctx.value.fillText('SCRATCH', c.width/2, c.height/2 + 6);
      ctx.value.globalCompositeOperation = 'destination-out';
    }

    function getEventPos(e) {
      const b = bounds();
      let x = 0, y = 0;
      if (e.touches && e.touches[0]) { x = e.touches[0].clientX; y = e.touches[0].clientY; }
      else { x = e.clientX; y = e.clientY; }
      return { x: x - b.left, y: y - b.top };
    }

    function start(e) {
      isDown.value = true;
      showCursor.value = true;
      drawAtEvent(e);
    }

    function scratchMove(e) {
      if (!isDown.value) { moveCursor(e); return; }
      drawAtEvent(e);
      throttledCheck();
    }

    function end() {
      isDown.value = false;
      showCursor.value = false;
      checkScratchPct();
    }

    function moveCursor(e) {
      const p = getEventPos(e);
      cursorX.value = Math.min(Math.max(0, p.x), canvas.value.width);
      cursorY.value = Math.min(Math.max(0, p.y), canvas.value.height);
      showCursor.value = true;
    }

    function drawAtEvent(e) {
      if (!ctx.value || !canvas.value) return;
      const p = getEventPos(e);
      const b = Math.max(24, Math.round(Math.min(canvas.value.width, canvas.value.height) * props.brushRatio));
      ctx.value.beginPath();
      ctx.value.arc(p.x, p.y, b, 0, Math.PI*2);
      ctx.value.fill();
    }

    function checkScratchPct() {
      if (!ctx.value || !canvas.value) return;
      try {
        const img = ctx.value.getImageData(0,0,canvas.value.width, canvas.value.height).data;
        let transparent = 0;
        for (let i = 3; i < img.length; i += 4) { if (img[i] === 0) transparent++; }
        const pct = transparent / (img.length / 4) * 100;
        scratchPct.value = Math.min(100, Math.round(pct));
        emitProgress();
        if (scratchPct.value >= props.threshold && !localRevealed.value) {
          localRevealed.value = true;
          setTimeout(() => { reveal(); emitRevealed(); }, 250);
        }
      } catch (e) {}
    }

    function throttledCheck() {
      // simple throttle
      if (throttledCheck._t) return;
      throttledCheck._t = setTimeout(() => { checkScratchPct(); clearTimeout(throttledCheck._t); throttledCheck._t = null; }, 120);
    }

    function emitProgress() { emit('progress', { boxIndex: props.boxIndex, scratchPct: scratchPct.value }); }
    function emitRevealed() { emit('revealed', { boxIndex: props.boxIndex, scratchPct: scratchPct.value }); }

    function reveal() {
      // hide canvas to show symbol
      if (canvas.value) canvas.value.style.display = 'none';
    }

    function onMove(e) { moveCursor(e); }
    function onLeave() { showCursor.value = false; }

    onMounted(() => {
      resizeCanvas();
      window.addEventListener('resize', resizeCanvas);
    });

    onBeforeUnmount(() => {
      window.removeEventListener('resize', resizeCanvas);
    });

    watch(() => props.revealed, (v) => {
      if (v) { localRevealed.value = true; if (canvas.value) canvas.value.style.display = 'none'; }
    });

    return { canvas, showCursor, cursorStyle: computed(() => ({ left: cursorX.value+'px', top: cursorY.value+'px', transform: 'translate(-50%, -50%)' })), onMove, onLeave, start, scratchMove, end };
  }
}
</script>

<style scoped>
.scratch-box{position:relative;min-width:160px;min-height:80px}
.scratch-canvas{position:relative;display:block;width:100%;height:100%}
.box-revealed{position:absolute;inset:0;display:flex;align-items:center;justify-content:center}
.symbol{font-size:24px;font-weight:bold}
.scratch-cursor{position:absolute;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.85);border:2px solid rgba(0,0,0,0.12);pointer-events:none}
</style>
