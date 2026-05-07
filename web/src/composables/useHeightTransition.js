/**
 * Provides JavaScript transition hooks for a smooth height-slide animation.
 * Spread the returned object onto a <Transition> element.
 *
 * Usage:
 *   const ht = useHeightTransition()
 *   <Transition v-bind="ht"> ... </Transition>
 */
export function useHeightTransition() {
  function onBeforeEnter(el) {
    el.style.height = '0'
    el.style.overflow = 'hidden'
    el.style.opacity = '0'
  }

  function onEnter(el, done) {
    el.offsetHeight // force reflow
    el.style.transition = 'height 0.25s ease, opacity 0.2s ease'
    el.style.height = el.scrollHeight + 'px'
    el.style.opacity = '1'
    el.addEventListener('transitionend', done, { once: true })
  }

  function onAfterEnter(el) {
    el.style.height = ''
    el.style.overflow = ''
    el.style.transition = ''
    el.style.opacity = ''
  }

  function onBeforeLeave(el) {
    el.style.height = el.scrollHeight + 'px'
    el.style.overflow = 'hidden'
  }

  function onLeave(el, done) {
    el.offsetHeight // force reflow
    el.style.transition = 'height 0.1s ease, opacity 0.15s ease, margin-bottom 0.1s ease'
    el.style.height = '0'
    el.style.opacity = '0'
    el.style.marginBottom = '0'
    el.addEventListener('transitionend', done, { once: true })
  }

  function onAfterLeave(el) {
    el.style.height = ''
    el.style.overflow = ''
    el.style.transition = ''
    el.style.opacity = ''
    el.style.marginBottom = ''
  }

  return {
    onBeforeEnter,
    onEnter,
    onAfterEnter,
    onBeforeLeave,
    onLeave,
    onAfterLeave,
  }
}
