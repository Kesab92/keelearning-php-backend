<template>
  <DashboardCard
    :class="{
      'cursor-progress': !stats.dashboard,
    }"
    class="text-xs-center">
    <template v-if="stats.dashboard">
      <div class="display-1 font-weight-medium">
        {{ stats.dashboard.usercounts.active | numberFormat }}
      </div>
      <div class="mb-2">
        aktive User<br>
        <!-- We have no subgrid yet, so it's not trivial to align different cards automatically -->
        <br>
      </div>
      <div class="grey--text">
        {{ stats.dashboard.usercounts.total | numberFormat }} User gesamt
      </div>
    </template>
    <template v-else>
      <div class="c-skeletonLoader -circle mx-auto"/>
      <div class="c-skeletonLoader -line mx-auto mt-2" style="width:50%"/>
      <div class="c-skeletonLoader -line mx-auto mt-2" style="width:75%"/>
    </template>
  </DashboardCard>
</template>

<script>
import {mapGetters} from 'vuex'
import DashboardCard from "./global/DashboardCard"

export default {
  computed: {
    ...mapGetters({
      stats: 'stats/stats',
    }),
  },
  components: {
    DashboardCard,
  }
}
</script>
