import Vue from 'vue'
import { persistState } from "../../logic/vuexStatePersister";


export default {
  setStatsData(state, {key, data}) {
    Vue.set(state.data, key, data)
    persistState('stats-data-' + window.VUEX_STATE.appId, state.data)
  },
}
