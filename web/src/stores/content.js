import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { api } from '../api/index.js'

function emptySlot() {
  return { entries: [], meta: { total: 0, limit: 30, offset: 0 }, loading: true }
}

/**
 * Per-collection content list cache with stale-while-revalidate.
 * Views read from cache[collection] immediately (showing stale rows while
 * fresh data loads in the background), so there is no blank loading flash
 * after the first visit to a collection.
 */
export const useContentStore = defineStore('content', () => {
  // Keyed by collection name. Each entry: { entries[], meta{}, loading }
  const cache = reactive({})

  function _ensure(collection) {
    if (!cache[collection]) cache[collection] = emptySlot()
    return cache[collection]
  }

  /**
   * Fetch (or re-fetch) a page of entries. If stale data exists it stays
   * visible until the response arrives — loading stays false while refreshing.
   */
  async function fetchList(collection, params = {}) {
    const slot = _ensure(collection)
    const isFirstLoad = slot.entries.length === 0
    if (isFirstLoad) slot.loading = true

    try {
      const res      = await api.content.list(collection, params)
      slot.entries   = res.data
      slot.meta      = res.meta
    } finally {
      slot.loading = false
    }
  }

  /** Drop cached rows so the next fetchList shows a spinner again. */
  function invalidate(collection) {
    if (cache[collection]) cache[collection].entries = []
  }

  return { cache, fetchList, invalidate }
})
