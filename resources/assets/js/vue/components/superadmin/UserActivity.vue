<template>
  <v-card class="pa-4">
    <form action="/superadmin/user-activity/download" method="GET">
      <p>
        Exportiert die User-Aktivität <strong>aller Apps</strong>.
      </p>
      <v-layout row wrap class="mt-4 mb-2">
        <v-flex xs6 class="mr-1">
          <div class="caption">Von</div>
          <DatePicker
            v-model="rangeFrom"
            value-type="YYYY-MM-DD"
            format="DD.MM.YYYY"
            :lang="lang"/>
        </v-flex>
        <v-flex>
          <div class="caption">Bis</div>
          <DatePicker
            v-model="rangeTo"
            value-type="YYYY-MM-DD"
            format="DD.MM.YYYY"
            :lang="lang"/>
        </v-flex>
      </v-layout>
      <input
        type="hidden"
        name="from"
        :value="rangeFrom" />
      <input
        type="hidden"
        name="to"
        :value="rangeTo" />
      <v-btn primary type="submit" class="mt-2">
        Download
      </v-btn>
    </form>
  </v-card>
</template>

<script>
import moment from 'moment'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'

export default {
  data() {
    return {
      lang: {
        formatLocale: {
          firstDayOfWeek: 1,
        },
      },
      rangeFrom: moment().subtract(30, 'days').format('YYYY-MM-DD'),
      rangeTo: moment().format('YYYY-MM-DD'),
    }
  },
  components: {
    DatePicker,
  },
}
</script>
