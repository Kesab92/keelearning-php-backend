const PREFIX = 'vuex-persisted-'

export function persistState(key, state) {
  try {
    localStorage.setItem(PREFIX + key, JSON.stringify(state))
  } catch (e) {
    console.error(e)
  }
}

export function fetchState(key) {
  try {
    const data = localStorage.getItem(PREFIX + key)
    if (typeof data !== 'string') {
      return null
    }
    return JSON.parse(data)
  } catch (e) {
    console.error(e)
    return null
  }
}
