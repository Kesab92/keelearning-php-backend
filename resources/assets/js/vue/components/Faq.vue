<template>
  <div>
    <v-snackbar
      :color="snackbarType"
      :top="true"
      v-model="snackbar">{{ snackbarMessage }}</v-snackbar>
    <v-card class="faq">
      <v-card-title>
        <div class="heading-container">
          <h1>Häufig gestellte Fragen</h1>
        </div>
      </v-card-title>

      <page-modal
        type="faq"
        v-if="superadmin"
        @update="loadPages">
        <v-btn
          slot
          color="success"
          class="createBtn">
          Neuen Eintrag erstellen
        </v-btn>
      </page-modal>


      <v-container>
        <div
          class="progress-container"
          v-if="loading">
          <v-progress-circular indeterminate/>
        </div>
        <v-expansion-panel
          popout
          v-else>
          <v-expansion-panel-content
            v-for="page in pages"
            :key="page.id">
            <div slot="header">
              <h4>{{ page.title }}</h4>
            </div>
            <v-card>
              <v-card-text>
                <page-editor
                  :display-title="false"
                  :superadmin="true"
                  :current-page="page"
                  @updatePage="updatePage(page)"
                  @removePage="removePage(page)"
                />
              </v-card-text>
            </v-card>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-container>
    </v-card>
  </div>
</template>

<script>

  export default {
    data() {
      return {
        pages: null,
        loading: false,
        snackbar: false,
        superadmin: false,
        snackbarType: null,
        snackbarMessage: null,
        defaultErrorMessage: 'Ein unerwarteter Fehler ist aufgetreten.'
      }
    },
    created() {
      this.loadPages()
    },
    methods: {
      loadPages() {
        this.loading = true
        axios.get('/backend/api/v1/helpdesk/faq').then(response => {
          if (response.data.success) {
            this.pages = response.data.data.pages
            this.superadmin = response.data.data.superadmin
          }
          this.loading = false
        })
      },
      updatePage(page) {
        axios.post('/backend/api/v1/helpdesk/pages/' + page.id + '/update', page).then(response => {
          if (response.data.success) {
            this.handleSnackbar('success', 'Dieser Eintrag wurde erfolgreich gespeichert.')
          } else {
            this.handleSnackbar('error', 'Dieser Eintrag konnte nicht gespeichert werden.')
          }
        }).catch(error => {
          this.handleSnackbar('error', this.defaultErrorMessage)
        })
      },
      removePage(page) {
        let confirmResult = confirm('Möchten Sie diesen Eintrag wirklich löschen?')
        if (!confirmResult) {
          return
        }

        axios.post('/backend/api/v1/helpdesk/pages/' + page.id + '/remove').then(response => {
          if (response.data.success) {
            this.handleSnackbar('success', 'Dieser Eintrag wurde erfolgreich gelöscht.')
          } else {
            this.handleSnackbar('error', 'Dieser Eintrag konnte nicht gelöscht werden.')
          }
        }).catch(error => {
          this.handleSnackbar('error', this.defaultErrorMessage)
        })
      },
      handleSnackbar(type, message) {
        this.snackbar = true
        this.snackbarType = type
        this.snackbarMessage = message
      }
    }
  }
</script>

<style lang="scss" scoped>
  #app {
    .article {
      background: #eee;
      padding: 10px;
      margin: 10px 0;
    }

    .faq {
      position: relative;

      .createBtn {
        top: 0;
        right: 0;
        position: absolute;
      }
    }

    .heading-container {
      width: 100%;
      text-align: center;
    }
  }
</style>
