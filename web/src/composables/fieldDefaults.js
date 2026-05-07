const SUPPORTED_DEFAULT_TYPES = new Set([
  'text',
  'textarea',
  'markdown',
  'number',
  'range',
  'boolean',
  'select',
  'date',
  'datetime',
  'json',
  'color',
])

export function supportsConfiguredDefault(config = {}) {
  return SUPPORTED_DEFAULT_TYPES.has(String(config?.type ?? 'text'))
}

export function hasConfiguredDefault(config = {}) {
  return supportsConfiguredDefault(config) && 'default' in config
}

export function fieldDefaultValue(config = {}) {
  if (hasConfiguredDefault(config)) {
    return cloneDefault(config.default)
  }

  if (config?.type === 'media') {
    return []
  }

  if ((config?.type === 'select' || config?.type === 'relation') && config.multiple) {
    return []
  }

  if (config?.type === 'repeater') {
    return []
  }

  return null
}

export function emptyConfiguredDefault(type, config = {}) {
  if (type === 'number' || type === 'range') return 0
  if (type === 'boolean') return false
  if (type === 'select' && config.multiple) return []
  if (type === 'json') return {}
  if (type === 'color') return '#000000'
  return ''
}

function cloneDefault(value) {
  if (Array.isArray(value)) {
    return value.map((item) => cloneDefault(item))
  }

  if (value && typeof value === 'object') {
    return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, cloneDefault(item)]))
  }

  return value
}
