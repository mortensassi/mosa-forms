import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
const path = require('path');


// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],

  build: {
    manifest: true
  },

  resolve: {
    alias: [
      {
        find: '@', replacement: path.resolve(__dirname, 'src'),
      },
      {
        find: '@styles', replacement: path.resolve(__dirname, 'src/assets/styles'),
      },
    ],
  },

  server: {
    host: true,
    https: true
  }
})