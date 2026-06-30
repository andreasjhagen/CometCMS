import { describe, expect, it } from 'vitest'
import {
  emptyConfiguredDefault,
  fieldDefaultValue,
  hasConfiguredDefault,
  supportsConfiguredDefault,
} from './fieldDefaults'

describe('fieldDefaults', () => {
  it('detects field types that support configured defaults', () => {
    expect(supportsConfiguredDefault({ type: 'text' })).toBe(true)
    expect(supportsConfiguredDefault({ type: 'html' })).toBe(true)
    expect(supportsConfiguredDefault({ type: 'media' })).toBe(false)
    expect(hasConfiguredDefault({ type: 'text', default: 'Hello' })).toBe(true)
    expect(hasConfiguredDefault({ type: 'media', default: [] })).toBe(false)
  })

  it('returns cloned configured default values', () => {
    const config = { type: 'json', default: { hero: { title: 'Hi' } } }
    const value = fieldDefaultValue(config)

    value.hero.title = 'Changed'

    expect(config.default.hero.title).toBe('Hi')
  })

  it('returns implicit empty arrays for collection-like fields', () => {
    expect(fieldDefaultValue({ type: 'media' })).toEqual([])
    expect(fieldDefaultValue({ type: 'select', multiple: true })).toEqual([])
    expect(fieldDefaultValue({ type: 'relation', multiple: true })).toEqual([])
    expect(fieldDefaultValue({ type: 'repeater' })).toEqual([])
  })

  it('creates appropriate empty configured defaults by field type', () => {
    expect(emptyConfiguredDefault('number')).toBe(0)
    expect(emptyConfiguredDefault('range')).toBe(0)
    expect(emptyConfiguredDefault('boolean')).toBe(false)
    expect(emptyConfiguredDefault('select', { multiple: true })).toEqual([])
    expect(emptyConfiguredDefault('json')).toEqual({})
    expect(emptyConfiguredDefault('color')).toBe('#000000')
    expect(emptyConfiguredDefault('text')).toBe('')
  })
})
