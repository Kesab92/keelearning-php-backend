<template>
  <div>
    <UserTabs />
    <Reportings
      :type="$constants.REPORTINGS.TYPE_USERS"
      root-route-name="stats.users.reportings"
      edit-route-name="stats.users.reportings.edit.general" />
  </div>
</template>
<script>
import UserTabs from "./UserTabs"
import Reportings from "../partials/reportings/Index"
export default {
  components: {
    UserTabs,
    Reportings,
  },
}
</script>
