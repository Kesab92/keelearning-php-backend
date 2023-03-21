<template>
  <div>
    <div class="o-dashboardGrid s-dashboardTopGrid">
      <div
        class="font-weight-medium headline my-3"
        style="grid-area: headerusers">
        User
      </div>
      <div
        class="font-weight-medium headline my-3"
        style="grid-area: headerurgent">
        Akut
      </div>
      <UserCountCard style="grid-area: usercount" />
      <UserLimitCard style="grid-area: userlimit" />
      <UserStatsCard style="grid-area: userstats" />
      <ActivitiesCard style="grid-area: activities" />
      <UrgentNotifications style="grid-area: urgentnotifications" />
    </div>
    <template v-if="stats.dashboard && stats.dashboard.mandatorycontent">
      <div class="font-weight-medium headline mt-5 mb-3">Pflichtinhalte</div>
      <div class="o-dashboardGrid">
        <MandatoryContentSummaryCard style="grid-row: 1; grid-column: 1/6;"/>
        <PassedMandatoryContentSummaryCard style="grid-row: 2; grid-column: 1/6;" />
        <MandatoryContentTableCard style="grid-row: 1/3; grid-column: 6/13;" />
      </div>
    </template>
    <div class="o-dashboardGrid -auto">
      <div v-if="stats.dashboard && stats.dashboard.quizgames">
        <div class="font-weight-medium headline mt-5 mb-3">Quiz</div>
        <div class="o-dashboardGrid -sub">
          <WeekStatsCard
            title="Spiele"
            tooltip="Zeigt die Anzahl der gestarteten Quiz-Spiele innerhalb einer Woche"
            :stats="stats.dashboard.quizgames.gamesPerWeek"
            style="grid-row: 1; grid-column: 1/3;" />
          <QuizGamesActivePlayersCard style="grid-row: 2; grid-column: 1/2;" />
          <QuizGamesBotPercentageCard style="grid-row: 2; grid-column: 2/3;" />
          <CallToAction
            href="/questions#/questions?create"
            style="grid-row: 3; grid-column: 1/3;">
            <v-icon>add</v-icon>
            Neue Frage hinzufügen
          </CallToAction>
        </div>
      </div>
      <div v-if="stats.dashboard && stats.dashboard.courses">
        <div class="font-weight-medium headline mt-5 mb-3">Kurse</div>
        <WeekStatsCard
          title="Kursteilnahmen"
          tooltip="Zeigt die Anzahl an Kursteilnahmen pro Woche"
          :stats="stats.dashboard.courses.participationsPerWeek" />
      </div>
      <div v-if="stats.dashboard && stats.dashboard.learningmaterials">
        <div class="font-weight-medium headline mt-5 mb-3">Mediathek</div>
        <WeekStatsCard
          title="Gesichtete Materialien"
          tooltip="Zeigt die Anzahl der geöffneten Dateien innerhalb einer Woche"
          :stats="stats.dashboard.learningmaterials.viewsPerWeek" />
      </div>
      <div v-if="stats.dashboard && stats.dashboard.news">
        <div class="font-weight-medium headline mt-5 mb-3">News</div>
        <WeekStatsCard
          title="Aufrufe"
          tooltip="Zeigt die Anzahl der geöffneten News innerhalb einer Woche"
          :stats="stats.dashboard.news.viewsPerWeek" />
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import UserCountCard from "./components/UserCountCard"
import UserLimitCard from "./components/UserLimitCard"
import UserStatsCard from "./components/UserStatsCard"
import MandatoryContentTableCard from "./components/MandatoryContentTableCard"
import CallToAction from "./components/global/CallToAction"
import QuizGamesActivePlayersCard from "./components/QuizGamesActivePlayersCard"
import WeekStatsCard from "./components/WeekStatsCard"
import QuizGamesBotPercentageCard from "./components/QuizGamesBotPercentageCard"
import MandatoryContentSummaryCard from "./components/MandatoryContentSummaryCard"
import PassedMandatoryContentSummaryCard from "./components/PassedMandatoryContentSummaryCard"
import ActivitiesCard from "./components/ActivitiesCard"
import UrgentNotifications from "./components/UrgentNotifications"

export default {
  created() {
    this.$store.dispatch('stats/loadStats', { key: 'dashboard'})
  },
  computed: {
    ...mapGetters({
      meta: 'stats/meta',
      stats: 'stats/stats',
    }),
  },
  components: {
    UserCountCard,
    UserLimitCard,
    UserStatsCard,
    MandatoryContentTableCard,
    ActivitiesCard,
    CallToAction,
    QuizGamesActivePlayersCard,
    WeekStatsCard,
    QuizGamesBotPercentageCard,
    MandatoryContentSummaryCard,
    PassedMandatoryContentSummaryCard,
    UrgentNotifications,
  },
}
</script>

<style lang="scss">
.s-dashboardTopGrid {
  grid-template-areas:
    "headerusers headerusers headerusers headerusers . . . . headerurgent headerurgent headerurgent headerurgent"
    "usercount usercount userlimit userlimit activities activities activities activities urgentnotifications urgentnotifications urgentnotifications urgentnotifications"
    "userstats userstats userstats userstats activities activities activities activities urgentnotifications urgentnotifications urgentnotifications urgentnotifications";
  grid-template-rows: auto 1fr auto; /* Make sure the UserLimit & UserCount cards grow, instead of the UserStats card */
}

@media screen and (max-width: 1279px) {
  .s-dashboardTopGrid {
    grid-template-areas:
      "headerurgent headerurgent headerurgent headerurgent headerurgent headerurgent . . . . . ."
      "urgentnotifications urgentnotifications urgentnotifications urgentnotifications urgentnotifications urgentnotifications . . . . . ."
      "urgentnotifications urgentnotifications urgentnotifications urgentnotifications urgentnotifications urgentnotifications . . . . . ."
      "headerusers headerusers headerusers headerusers headerusers headerusers . . . . . ."
      "usercount usercount usercount userlimit userlimit userlimit activities activities activities activities activities activities"
      "userstats userstats userstats userstats userstats userstats activities activities activities activities activities activities";
    grid-template-rows: auto auto auto auto 1fr auto; /* Make sure the UserLimit & UserCount cards grow, instead of the UserStats card */
  }
}
</style>
