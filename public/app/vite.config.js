import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
const path = require('path')
import legacy from '@vitejs/plugin-legacy'


// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    legacy({
      targets: ['defaults', 'not IE 11']
    })
  ],

  build: {
    manifest: true,
  },

  resolve: {
    alias: [
      {
        find: '@', replacement: path.resolve(__dirname, 'src'),
      },
      {
        find: '@styles', replacement: path.resolve(__dirname, 'src/assets/scss'),
      },
    ],
  },

  css: {
    preprocessorOptions: {
      scss: { additionalData: `@import "@/assets/scss/app";` },
    },
  },

  server: {
    host: true,
    https: true
  }
})
