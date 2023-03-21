export default {
  meta: (state) => {
    return Object.entries(state.data).reduce((result, [key, data]) => {
      if(data.meta) {
        result[key] = data.meta
      }
      return result
    }, {})
  },
  stats: (state) => {
    return Object.entries(state.data).reduce((result, [key, data]) => {
      if(data.stats) {
        result[key] = data.stats
      }
      return result
    }, {})
  },
}
