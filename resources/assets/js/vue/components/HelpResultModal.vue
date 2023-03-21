<template>
  <span>
    <v-dialog v-model="dialog" width="50%">
      <v-btn
        :disabled="keyword === null || keyword.trim().length === 0"
        type="submit"
        slot="activator"
        color="primary"
        dark>
        Suchen
      </v-btn>

      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>Suchergebnisse für "{{ keyword }}"</v-card-title>

        <v-card-text>
          <div class="progress-container" v-if="loading">
            <v-progress-circular indeterminate></v-progress-circular>
          </div>
          <div v-else>
            <help-result-entry
              v-if="results.length > 0"
              v-for="page in results"
              :page="page"
              :key="page.id"
            />
            <div v-else>Die Suche ergab leider keine Treffer.</div>
          </div>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            flat
            @click="dialog = false">
            Schließen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </span>
</template>

<script>
  export default {
    props: [ 'keyword', 'results', 'loading' ],
    data() {
      return {
        dialog: false
      }
    }
  }
</script>