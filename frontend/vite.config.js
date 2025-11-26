import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  base:
    process.env.NODE_ENV === 'production'
      ? '/dist/' // set the base to '/dist/' when in production mode
      : '/',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    port: 5173,
    proxy: {
      '/backend': {
        target: 'http://pft-php-server:80/personal-finance-tracker/',
        changeOrigin: true,
        // rewrite: (path) => path.replace(/^\/backend/, ''),
      },
    },
  },
})