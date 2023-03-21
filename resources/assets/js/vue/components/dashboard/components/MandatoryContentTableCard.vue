<template>
  <DashboardCard>
    <DashboardTable
      v-if="meta.dashboard.mandatorycourses && meta.dashboard.mandatorycourses.length"
      :items="tableItems"
      :headers="headers"
      :custom-sort="customSort"
      :pagination="pagination"
      sticky-header>
      <template
        #items="{ item }">
        <tr
          @click="openMandatoryContent(item)"
          :class="{clickable: hasRightsToCourse}">
          <td class="grey--text body-2 font-weight-medium">
            Kurs
          </td>
          <td class="body-2 font-weight-medium">
            {{ item.title }}
          </td>
          <td class="body-2 font-weight-medium text-xs-center">
            <v-chip
              v-if="item.available_until"
              label
              outline
              :color="deadlineColor(item.available_until) || 'transparent'"
              :text-color="deadlineColor(item.available_until) || 'black'"
            >
              {{ item.available_until | date }}
            </v-chip>
            <template v-else>
              –
            </template>
          </td>
          <td class="body-2 font-weight-medium">
            <ProgressBar :value="item.user_finished_percentage"/>
          </td>
          <td class="body-2 font-weight-medium">{{ item.user_finished_count }} / {{ item.user_count }}</td>
        </tr>
      </template>
    </DashboardTable>
    <div
      v-else
      class="grey--text text-xs-center">
      Noch keine Daten vorhanden.
    </div>
  </DashboardCard>
</template>

<script>
import moment from 'moment'
import {mapGetters} from 'vuex'
import {compareAsc, parse} from "date-fns"
import DashboardTable from "./global/DashboardTable"
import DashboardCard from "./global/DashboardCard"
import ProgressBar from "./global/ProgressBar"

export default {
  data() {
    return {
      headers: [
        {
          text: "Art",
          value: "type",
          sortable: false,
        },
        {
          text: "Name",
          value: "title",
          sortable: true,
        },
        {
          text: "Deadline",
          value: "available_until",
          align: "center",
          sortable: true,
        },
        {
          text: "Absolviert",
          value: "user_finished_percentage",
          width: '200px',
          sortable: true,
        },
        {
          text: "User",
          value: "user_count",
          sortable: true,
        },
      ],
      pagination: {
        sortBy: 'available_until',
        rowsPerPage: -1,
      },
    }
  },
  computed: {
    ...mapGetters({
      meta: 'stats/meta',
      allStats: 'stats/stats',
      myRights: 'app/myRights',
    }),
    stats() {
      return this.allStats.dashboard.mandatorycontent
    },
    tableItems() {
      return this.meta.dashboard.mandatorycourses.map((course) => {
        return {
          ...course,
          user_count: this.stats.mandatoryCoursesUserCounts[course.id],
          user_finished_count: this.stats.mandatoryCoursesPassed[course.id] || 0,
          user_finished_percentage: this.stats.mandatoryCoursesUserCounts[course.id] ? (this.stats.mandatoryCoursesPassed[course.id] || 0) / this.stats.mandatoryCoursesUserCounts[course.id] : 0,
        }
      })
    },
    hasRightsToCourse() {
      return this.myRights['courses-edit'] || this.myRights['courses-view']
    },
  },
  methods: {
    openMandatoryContent(mandatoryContent) {
      if(this.hasRightsToCourse) {
        window.location.href=`courses#/courses/${mandatoryContent.id}/general`
      }
    },
    deadlineColor(availableUntil) {
      let deadline = moment(availableUntil)
      if (deadline.isBefore()) {
        return 'red'
      }
      if (deadline.subtract(3, 'days').isBefore()) {
        return 'orange'
      }
      return null
    },
    customSort(items, index, isDesc) {
      items.sort((a, b) => {
        if (index === "available_until") {
          const aAvailableUntil = parse(a.available_until, 'yyyy-MM-dd HH:mm:ss', new Date())
          const bAvailableUntil = parse(b.available_until, 'yyyy-MM-dd HH:mm:ss', new Date())
          const now = new Date()
          let descModifier = 1

          // If 2 dates are null, they are equal
          if (!a.available_until && !b.available_until) {
            return 0
          }

          // revers order for the DESC state
          if(isDesc) {
            descModifier = -1
          }

          // if b.available_until is only null
          if (a.available_until && !b.available_until) {
            // the future dates should be above null dates
            if (aAvailableUntil > now) {
              return -1 * descModifier
            }
            // the past dates should be below null dates
            return descModifier
          }

          // if a.available_until is only null
          if (!a.available_until && b.available_until) {
            // the future dates should be above null dates
            if (bAvailableUntil > now) {
              return descModifier
            }
            // the past dates should be below null dates
            return -1 * descModifier
          }

          // if the 2 dates are future, the earlier date should be above
          if (aAvailableUntil > now && bAvailableUntil > now) {
            return descModifier * compareAsc(aAvailableUntil, bAvailableUntil)
          }
          // if on of the dates is past, the earlier date should be below
          return descModifier * compareAsc(bAvailableUntil, aAvailableUntil)
        } else {
          if (!isDesc) {
            return a[index] < b[index] ? -1 : 1
          } else {
            return b[index] < a[index] ? -1 : 1
          }
        }
      })
      return items
    }
  },
  components: {
    DashboardCard,
    DashboardTable,
    ProgressBar,
  }
}
</script>
