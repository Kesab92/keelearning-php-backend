import { fetchState } from '../../logic/vuexStatePersister'

export default {
  data: fetchState('stats-data-' + window.VUEX_STATE.appId) || {},
}
