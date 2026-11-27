import { onBeforeUnmount, ref } from 'vue'

export function useFlashMessage(durationMs = 3500) {
  const message = ref('')
  let timer = null

  function clear() {
    if (timer) {
      clearTimeout(timer)
      timer = null
    }
    message.value = ''
  }

  function flash(text) {
    clear()
    if (!text) return
    message.value = text
    timer = setTimeout(() => {
      message.value = ''
      timer = null
    }, durationMs)
  }

  onBeforeUnmount(clear)

  return { message, flash, clear }
}
