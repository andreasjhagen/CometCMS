import { ref } from 'vue'

export const DEFAULT_ADMIN_LOCALE = 'en'

const STORAGE_KEY = 'cometcms-admin-locale'
const languageModules = import.meta.glob('./lang/*.{js,json}', { eager: true })
const browserStorage = typeof localStorage !== 'undefined' && typeof localStorage.getItem === 'function'
  ? localStorage
  : null

function localeFromPath(path) {
  return path
    .split('/')
    .pop()
    ?.replace(/\.(js|json)$/i, '')
    .toLowerCase()
    .replace('_', '-') ?? ''
}

function displayNameForLocale(locale) {
  try {
    return new Intl.DisplayNames([locale, DEFAULT_ADMIN_LOCALE], { type: 'language' }).of(locale) ?? locale
  } catch {
    return locale
  }
}

function normalizeLanguageModule(module, locale) {
  const config = module.default ?? module
  const messages = config.messages ?? config

  return {
    value: locale,
    label: config.label ?? displayNameForLocale(locale),
    messages: messages && typeof messages === 'object' ? messages : {},
  }
}

function loadLanguages() {
  const languages = Object.entries(languageModules)
    .map(([path, module]) => normalizeLanguageModule(module, localeFromPath(path)))
    .filter((language) => language.value && Object.keys(language.messages).length > 0)

  languages.sort((a, b) => {
    if (a.value === DEFAULT_ADMIN_LOCALE) return -1
    if (b.value === DEFAULT_ADMIN_LOCALE) return 1
    return a.label.localeCompare(b.label)
  })

  return languages
}

export const ADMIN_LANGUAGES = loadLanguages()
export const messages = Object.fromEntries(
  ADMIN_LANGUAGES.map((language) => [language.value, language.messages])
)

const currentLocale = ref(resolveInitialLocale())

function resolveInitialLocale() {
  if (browserStorage) {
    const storedValue = browserStorage.getItem(STORAGE_KEY)
    const stored = normalizeLocale(storedValue)
    if (storedValue !== null) return stored
  }

  if (typeof navigator !== 'undefined') {
    return normalizeLocale(navigator.language)
  }

  return DEFAULT_ADMIN_LOCALE
}

export function normalizeLocale(locale) {
  const value = String(locale ?? '').trim().toLowerCase().replace('_', '-')
  const direct = ADMIN_LANGUAGES.find((item) => item.value === value)
  if (direct) return direct.value

  const base = value.split('-')[0]
  return ADMIN_LANGUAGES.some((item) => item.value === base) ? base : DEFAULT_ADMIN_LOCALE
}

export function setLocale(locale, options = {}) {
  const normalized = normalizeLocale(locale)
  currentLocale.value = normalized

  if (typeof document !== 'undefined') {
    document.documentElement.lang = normalized
  }

  if (options.persist !== false && browserStorage) {
    browserStorage.setItem(STORAGE_KEY, normalized)
  }
}

export function t(key, replacements = {}) {
  const text = messages[currentLocale.value]?.[key] ?? messages[DEFAULT_ADMIN_LOCALE]?.[key] ?? key

  return Object.entries(replacements).reduce(
    (value, [name, replacement]) => value.replaceAll(`{${name}}`, String(replacement)),
    text
  )
}

export function useI18n() {
  return {
    locale: currentLocale,
    languageOptions: ADMIN_LANGUAGES,
    setLocale,
    t,
  }
}

setLocale(currentLocale.value, { persist: false })
