import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  let nextId = 0

  function show(message, type = 'success') {
    const id = nextId++
    toasts.value.push({ id, message, type })
    setTimeout(() => remove(id), 4000)
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (msg) => show(msg, 'success')
  const error   = (msg) => show(msg, 'error')

  return { toasts, success, error, remove }
})
