<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
             <!-- Logo / wordmark -->
        <img
          v-if="!logoFailed"
          src="/img/cms-logo-black.png"
          alt="CometCMS"
          class="h-14 mx-auto object-contain"
          @error="logoFailed = true"
        />
        <h1 v-else class="text-2xl font-bold text-slate-900">CometCMS</h1>
      </div>

      <div class="card p-6 mb-4">
        <div class="flex items-start gap-3">
          <Icon icon="mdi:check-circle" class="h-6 w-6 text-green-500" />
          <div class="flex-1">
            <p class="text-sm font-medium text-slate-800">{{ t('setup.successTitle') }}</p>
            <p class="text-slate-500 mt-1 text-sm">{{ t('setup.successBody') }}</p>
            <p class="text-sm text-slate-500 mt-1">
              {{ t('setup.instructions') }}
              <span class="font-medium text-slate-700">{{ t('setup.usernameLocked') }}</span>
              {{ t('setup.afterSetup') }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <form @submit.prevent="handleSetup" class="space-y-4">
          <div v-if="errorMsg" class="p-3 bg-red-50 text-red-700 rounded-lg text-sm">
            {{ errorMsg }}
          </div>

          <div>
            <label class="form-label">{{ t('login.username') }}</label>
            <input
              v-model="username"
              type="text"
              required
              autofocus
              class="form-input w-full rounded-lg border-slate-300"
            />
          </div>

          <div>
            <label class="form-label">{{ t('login.password') }} <span class="text-slate-400 font-normal">{{ t('setup.passwordMin') }}</span></label>
            <input
              v-model="password"
              type="password"
              required
              minlength="8"
              class="form-input w-full rounded-lg border-slate-300"
            />
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full justify-center">
            <span v-if="loading">{{ t('setup.creating') }}</span>
            <span v-else>{{ t('setup.createAccount') }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import { Icon } from '@iconify/vue'
import { useI18n } from '../i18n/index.js'

const auth     = useAuthStore()
const router   = useRouter()
const { t } = useI18n()
const username = ref('admin')
const password = ref('')
const loading  = ref(false)
const errorMsg = ref('')
const logoFailed = ref(false)

async function handleSetup() {
  errorMsg.value = ''
  loading.value  = true
  try {
    await auth.setup(username.value, password.value)
    router.push('/dashboard')
  } catch (err) {
    errorMsg.value = err.message ?? t('setup.failed')
  } finally {
    loading.value = false
  }
}
</script>
