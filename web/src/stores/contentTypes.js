import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../api/index.js'

/**
 * Caches the content-type list so the sidebar never re-fetches from scratch
 * on navigation. Pattern: show stale data immediately, refresh in background.
 */
export const useContentTypesStore = defineStore('contentTypes', () => {
  const list    = ref([])
  let fetched   = false
  let inFlight  = null

  async function _doFetch() {
    if (inFlight) return inFlight
    inFlight = api.contentTypes.list()
      .then(res => { list.value = res.data; fetched = true })
      .catch(() => {})
      .finally(() => { inFlight = null })
    return inFlight
  }

  /**
   * Call from onMounted. If we already have data, kicks off a background
   * refresh and returns immediately (no await needed). On first call, waits
   * for the data so the sidebar has types before it renders.
   */
  async function fetch() {
    if (fetched) {
      _doFetch()   // background refresh – don't await
      return
    }
    await _doFetch()
  }

  /** Call after creating or deleting a content type. */
  function invalidate() {
    fetched = false
  }

  function setList(types) {
    list.value = Array.isArray(types) ? types : []
    fetched = true
  }

  return { list, fetch, invalidate, setList }
})
