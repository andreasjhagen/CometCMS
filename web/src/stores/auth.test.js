import { describe, expect, it } from 'vitest'
import { allowsPermission } from './permissions.js'

describe('auth permissions', () => {
  it('allows matching action and resource grants', () => {
    const grants = [
      { effect: 'allow', actions: ['content.*'], resources: ['content:posts:*'] },
    ]

    expect(allowsPermission(grants, 'content.update', 'content:posts:welcome')).toBe(true)
    expect(allowsPermission(grants, 'media.update', 'media:hero.jpg')).toBe(false)
  })

  it('lets deny grants override broader allows', () => {
    const grants = [
      { effect: 'allow', actions: ['content.*'], resources: ['content:*'] },
      { effect: 'deny', actions: ['content.delete'], resources: ['content:posts:locked'] },
    ]

    expect(allowsPermission(grants, 'content.update', 'content:posts:locked')).toBe(true)
    expect(allowsPermission(grants, 'content.delete', 'content:posts:locked')).toBe(false)
  })

  it('allows global resource checks when no resource is requested', () => {
    const grants = [
      { effect: 'allow', actions: ['dashboard.read'], resources: ['*'] },
    ]

    expect(allowsPermission(grants, 'dashboard.read')).toBe(true)
    expect(allowsPermission(grants, 'roles.delete')).toBe(false)
  })
})
