<template>
  <div v-if="visible" class="captcha-overlay">
    <div class="captcha-modal">
      <h4 class="mb-3">Security Check</h4>

      <p>Please solve this to continue:</p>

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
        <button class="btn btn-secondary me-2" @click="regen">⟳</button>
        <button class="btn btn-primary" @click="submit">Continue</button>
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
  padding: 25px;
  border-radius: 10px;
  width: 320px;
  box-shadow: 0 0 20px rgba(0,0,0,0.3);
  animation: popup 0.25s ease-out;
}

.captcha-box {
  font-size: 28px;
  font-weight: bold;
  background: #f7f7f7;
  border: 1px solid #ddd;
  padding: 10px;
  margin: 15px 0;
  border-radius: 6px;
  text-align: center;
  user-select: none;
}

@keyframes popup {
  from { transform: scale(0.85); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}
</style>
