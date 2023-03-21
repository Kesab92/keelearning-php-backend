<template>
  <div>
    <Reportings
      :type="$constants.REPORTINGS.TYPE_QUIZ"
      root-route-name="stats.quiz.reportings"
      edit-route-name="stats.quiz.reportings.edit.general" />
  </div>
</template>
<script>
import Reportings from "../partials/reportings/Index"
export default {
  components: {
    Reportings,
  },
}
</script>
