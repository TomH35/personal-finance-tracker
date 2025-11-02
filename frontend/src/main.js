import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import { useLoginStore } from './stores/loginStore'
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Load JWT from localStorage on app startup
const loginStore = useLoginStore()
loginStore.loadJwt()
app.mount('#app')
