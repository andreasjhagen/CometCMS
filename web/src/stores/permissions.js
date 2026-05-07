export function allowsPermission(grants = [], action, resource = null) {
  function globMatch(pattern, value) {
    if (pattern === '*' || pattern === value) return true
    const regex = new RegExp(
      '^' + String(pattern).replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*') + '$'
    )
    return regex.test(value)
  }

  function matchesAction(grant) {
    const actions = grant.actions ?? []
    return actions.some((a) => globMatch(a, action))
  }

  function matchesResource(grant) {
    if (resource === null) return true
    const resources = grant.resources ?? []
    return resources.some((r) => globMatch(r, resource))
  }

  const matches = (grant) => matchesAction(grant) && matchesResource(grant)

  if (grants.some((grant) => grant.effect === 'deny' && matches(grant))) return false

  return grants.some((grant) => grant.effect !== 'deny' && matches(grant))
}
