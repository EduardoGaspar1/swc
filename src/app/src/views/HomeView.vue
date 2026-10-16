<script setup>
import { onMounted, ref } from 'vue'
import { http } from '@/api/http'

const status = ref('a comprobar...')

onMounted(async () => {
  try {
    const { data } = await http.get('/health')
    status.value = data.status
  } catch {
    status.value = 'sin conexión con la API'
  }
})
</script>

<template>
  <main>
    <h1>SWC — Smart WAN Control</h1>
    <p>API: {{ status }}</p>
  </main>
</template>
