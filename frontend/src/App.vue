<template>
  <div id="app">
    <Navigation />
    <main>
      <RouterView />
    </main>
    <Footer />
  </div>
</template>

<script>
import { RouterView } from 'vue-router'
import Navigation from './components/Navigation.vue'
import Footer from './components/Footer.vue'

export default {
  components: {
    Navigation,
    Footer,
  },
}
</script>
