<template>
  <v-dialog
    v-model="open"
    :persistent="true"
    width="500"
  >
    <v-card>
      <v-card-title
        class="headline grey lighten-2"
        primary-title
      >
        <template v-if="!importData || (importData.progress === 0 && importData.status <= 0)">
          Import ist in der Warteschlange
        </template>
        <template v-else-if="importData.status === 0">
          Import wird ausgeführt...
        </template>
        <template v-else-if="importData.status === 1">
          Import erfolgreich abgeschlossen
        </template>
        <template v-else>
          Fehler beim Import
        </template>
      </v-card-title>

      <v-card-text v-if="!importData || (importData.progress === 0 && importData.status <= 0)">
        Bitte haben Sie einen Moment Geduld bis der Import gestartet wird.
      </v-card-text>
      <v-card-text v-else>
        <p class="text-xs-center mt-2 mb-0">
          <template v-if="importData.status === 0">
            Der Import wird aktuell ausgeführt. Fortschritt:
          </template>
          <template v-else-if="importData.status === 1">
            <v-icon color="green" large style="vertical-align: -10px">check</v-icon> Der Import wurde erfolgreich durchgeführt.<br>
            <v-btn color="success" href="/import">Zurück zum Import-Dashboard</v-btn>
          </template>
          <template v-else>
            <v-icon color="red" large style="vertical-align: -10px">error_outline</v-icon> Beim Importieren Ihrer Daten gab es ein Problem. Bitte wenden Sie sich an den Support: <a href="mailto:support@keeunit.de">support@keeunit.de</a><br>
            <v-btn color="error" href="/import">Zurück zum Import-Dashboard</v-btn>
          </template>
        </p>
        <v-container grid-list-sm px-0>
          <v-layout row>
            <v-flex class="" :key="idx" v-for="(step, idx) in steps">
              <v-progress-linear v-model="steps[idx]"></v-progress-linear>
            </v-flex>
          </v-layout>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>

</template>

<script>
  export default {
    props: ['open', 'importId'],
    data() {
      return {
        'importData': null,
        steps: []
      }
    },
    watch: {
      open() {
        if(this.open) {
          this.fetchData()
        }
      },
    },
    methods: {
      fetchData() {
        if (!this.importId) {
            return
        }
        axios.get("/backend/api/v1/imports/" + this.importId).then(response => {
          this.importData = response.data.import
          this.setSteps()
          if(parseInt(this.importData.status) === 0) {
            window.setTimeout(this.fetchData, 1000)
          }
        })
      },
      getStepProgress(step) {
        let lastFullStep = parseInt(this.importData.progress)
        if(lastFullStep > step) {
          return 100
        }
        if(lastFullStep === step) {
          return (this.importData.progress - lastFullStep) * 100
        }
        return 0
      },
      setSteps() {
        for(let i = 0;i < this.importData.steps; i++) {
          this.steps[i] = this.getStepProgress(i)
        }
      },
    },
  }
</script>
