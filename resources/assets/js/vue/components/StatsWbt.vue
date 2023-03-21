<template>
  <div>
    <v-card>
      <v-card-title primary-title>
        <div class="headline">
          WBT Statistiken
        </div>
        <v-spacer />
        <v-text-field
          append-icon="search"
          placeholder="Suche"
          single-line
          v-model="search"
        />
      </v-card-title>
      <WBTStatsTable
        :search="search" />
    </v-card>
  </div>
</template>

<script>
import WBTStatsTable from "./partials/learningmaterials/WBTStatsTable"

export default {
  data() {
    return {
      search: null,
    }
  },
  components: {
    WBTStatsTable,
  },
}
</script>
