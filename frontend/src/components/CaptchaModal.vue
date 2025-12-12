<template>
  <div v-if="visible" class="captcha-overlay">
    <div class="captcha-modal">
      <div class="d-flex align-items-center mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#1D2A5B" class="me-2" viewBox="0 0 16 16">
          <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
        </svg>
        <h4 class="mb-0" style="color: #1D2A5B;">Security Check</h4>
      </div>

      <p class="text-secondary">Please solve this to continue:</p>

      <div class="captcha-box">
        {{ a }} + {{ b }} =
      </div>

      <input
        type="number"
        class="form-control mb-3"
        v-model="answer"
        placeholder="Your answer"
      />

      <div class="text-end">
        <button class="btn btn-outline-secondary me-2" @click="regen">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
          </svg>
        </button>
        <button class="btn" style="background-color: #1D2A5B; color: white; border: none;" @click="submit">Continue</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  visible: Boolean,
})

const emit = defineEmits(['solved'])

const a = ref(0)
const b = ref(0)
const answer = ref(null)

function regen() {
  a.value = Math.floor(Math.random() * 9) + 1
  b.value = Math.floor(Math.random() * 9) + 1
  answer.value = null
}

watch(() => props.visible, (v) => {
  if (v) regen()
})

function submit() {
  if (parseInt(answer.value) === a.value + b.value) {
    emit('solved', {
      captcha_a: a.value,
      captcha_b: b.value,
      captcha_answer: answer.value
    })
  } else {
    alert("Incorrect captcha, try again.")
    regen()
  }
}
</script>

<style scoped>
.captcha-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
}

.captcha-modal {
  background: white;
  padding: 30px;
  border-radius: 12px;
  width: 360px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e8eaed;
  animation: popup 0.25s ease-out;
}

.captcha-box {
  font-size: 28px;
  font-weight: bold;
  background: #f8f9fa;
  border: 2px solid #1D2A5B;
  color: #1D2A5B;
  padding: 20px;
  margin: 15px 0;
  border-radius: 8px;
  text-align: center;
  user-select: none;
}

@keyframes popup {
  from { transform: scale(0.85); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}
</style>
