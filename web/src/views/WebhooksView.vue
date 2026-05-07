<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-900">{{ t('webhooks.title') }}</h1>
    </div>

    <LoadingSpinner v-if="loading" />

    <template v-else>
      <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-base font-semibold text-slate-800">{{ t('webhooks.outbound') }}</h3>
            <p class="text-sm text-slate-500 mt-0.5">
              {{ t('webhooks.description') }}
              <a href="/docs/guide/webhooks" target="_blank" class="text-theme-600 hover:underline">{{ t('webhooks.documentation') }}</a>
            </p>
          </div>
          <button @click="addWebhook" class="btn-secondary text-sm">
            {{ t('webhooks.add') }}
          </button>
        </div>

        <div v-if="webhooks.length === 0" class="text-sm text-slate-400 italic py-2">
          {{ t('webhooks.empty') }}
        </div>

        <div v-else class="space-y-5">
          <div
            v-for="(hook, index) in webhooks"
            :key="index"
            class="border border-slate-200 rounded-lg p-4 space-y-4"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <ToggleSwitch v-model="hook.enabled" />
                <span class="text-sm font-medium" :class="hook.enabled ? 'text-slate-700' : 'text-slate-400'">{{ t('webhooks.item', { number: index + 1 }) }}</span>
              </div>
              <div class="flex items-center gap-3">
                <button
                  @click="runWebhook(hook, index)"
                  :disabled="runningWebhooks[index]"
                  class="btn-secondary text-xs"
                >
                  {{ runningWebhooks[index] ? t('webhooks.running') : t('webhooks.runNow') }}
                </button>
                <button
                  @click="removeWebhook(index)"
                  class="text-xs text-red-500 hover:text-red-700 transition-colors"
                >
                  {{ t('webhooks.remove') }}
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">{{ t('webhooks.url') }} <span class="text-red-500">*</span></label>
                <input
                  v-model="hook.url"
                  type="url"
                  placeholder="https://example.com/build-hook"
                  class="form-input w-full rounded-lg border-slate-300 text-sm"
                />
              </div>
              <div>
                <label class="form-label">{{ t('webhooks.secret') }}</label>
                <input
                  v-model="hook.secret"
                  type="text"
                  :placeholder="t('webhooks.secretPlaceholder')"
                  class="form-input w-full rounded-lg border-slate-300 text-sm"
                />
              </div>
            </div>

            <div>
              <label class="form-label mb-2">{{ t('webhooks.triggerOn') }}</label>
              <div class="flex flex-wrap gap-x-5 gap-y-2">
                <label
                  v-for="event in WEBHOOK_EVENTS"
                  :key="event"
                  class="inline-flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="event"
                    v-model="hook.events"
                    class="form-checkbox rounded border-slate-300 text-theme-600"
                  />
                  <span class="text-sm text-slate-600">{{ event }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div v-if="saveError" class="mt-4 text-sm text-red-600">{{ saveError }}</div>

        <div class="mt-5 flex items-center gap-3">
          <button @click="handleSave" :disabled="saving" class="btn-primary">
            {{ saving ? t('common.saving') : t('webhooks.save') }}
          </button>
          <Transition name="fade">
            <span v-if="saved" class="text-sm text-green-600">{{ t('webhooks.savedInline') }}</span>
          </Transition>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../api/index.js'
import { useToastStore } from '../stores/toast.js'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import ToggleSwitch from '../components/ToggleSwitch.vue'
import { useI18n } from '../i18n/index.js'

const toast = useToastStore()
const { t } = useI18n()

const WEBHOOK_EVENTS = [
  'content.created',
  'content.updated',
  'content.published',
  'content.unpublished',
  'content.deleted',
  'content.restored',
]

const loading = ref(true)
const saving = ref(false)
const saved = ref(false)
const saveError = ref('')
const webhooks = ref([])
const runningWebhooks = ref({})

onMounted(async () => {
  try {
    const res = await api.webhooks.get()
    webhooks.value = (res.data?.webhooks ?? []).map(normalizeHook)
  } catch {
    toast.error(t('webhooks.loadFailed'))
  } finally {
    loading.value = false
  }
})

function normalizeHook(hook = {}) {
  return {
    url: hook.url ?? '',
    secret: hook.secret ?? '',
    events: Array.isArray(hook.events) ? [...hook.events] : [],
    enabled: hook.enabled !== false,
  }
}

function addWebhook() {
  webhooks.value.push(normalizeHook())
}

function removeWebhook(index) {
  webhooks.value.splice(index, 1)
  delete runningWebhooks.value[index]
}

async function handleSave() {
  saving.value = true
  saved.value = false
  saveError.value = ''

  for (const [i, hook] of webhooks.value.entries()) {
    if (!hook.url.trim()) {
      saveError.value = t('webhooks.urlRequired', { number: i + 1 })
      saving.value = false
      return
    }
  }

  try {
    const res = await api.webhooks.update({ webhooks: webhooks.value })
    webhooks.value = (res.data?.webhooks ?? []).map(normalizeHook)
    saved.value = true
    toast.success(t('webhooks.saved'))
    setTimeout(() => { saved.value = false }, 3000)
  } catch (err) {
    saveError.value = err.message ?? t('webhooks.saveFailed')
  } finally {
    saving.value = false
  }
}

async function runWebhook(hook, index) {
  runningWebhooks.value = { ...runningWebhooks.value, [index]: true }

  try {
    await api.webhooks.run(hook)
    toast.success(t('webhooks.runSuccess', { number: index + 1 }))
  } catch (err) {
    toast.error(err.message ?? t('webhooks.runFailed', { number: index + 1 }))
  } finally {
    const next = { ...runningWebhooks.value }
    delete next[index]
    runningWebhooks.value = next
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
