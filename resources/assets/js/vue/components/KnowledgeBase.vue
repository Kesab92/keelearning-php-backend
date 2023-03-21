<template>
  <div class="knowledge-container">
    <v-snackbar v-model="snackbar" :top="true" :color="snackbarType">{{ snackbarMessage }}</v-snackbar>
    <div class="content">
      <v-card>
        <v-btn href="/help">Zurück zur Helpdesk-Startseite</v-btn>
      </v-card>
      <v-card class="inner-content">
        <div
          class="progress-container"
          v-if="currentPage === null || categories === null">
          <v-progress-circular indeterminate></v-progress-circular>
        </div>
        <page-editor
          v-else
          :categories="categories"
          :superadmin="superadmin"
          :currentPage="currentPage"
          @updatePage="updatePage"
          @removePage="removePage"
        />
        </v-card>
    </div>
    <v-card class="sub-navigation">
      <knowledge-submenu
        v-if="categories"
        :categories="categories"
        :superadmin="superadmin"
        @error="handleError"
        @update="loadCategories"
      />
      <div class="progress-container" v-else>
        <v-progress-circular indeterminate></v-progress-circular>
      </div>
    </v-card>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        workMode: false,
        currentPage: null,
        superadmin: false,
        snackbar: false,
        snackbarType: null,
        snackbarMessage: null,
        categories: null,
      }
    },
    methods: {
      handleError(message) {
        this.snackbar = true
        this.snackbarType = 'error'
        this.snackbarMessage = message
      },
      handleSuccess(message) {
        this.snackbar = true
        this.snackbarType = 'success'
        this.snackbarMessage = message
      },
      query(name) {
        let query = window.location.search.substring(1);
        let vars = query.split('&');
        for (let i = 0; i < vars.length; i++) {
          let pair = vars[i].split('=');
          if (decodeURIComponent(pair[0]) == name) {
            return decodeURIComponent(pair[1]);
          }
        }
      },
      loadPage() {
        let id = this.query('page')
        axios.get('/backend/api/v1/helpdesk/knowledge/' + id).then(response => {
          if (response.data.success) {
            this.currentPage = response.data.data.page
            this.superadmin =  response.data.data.superadmin
          }
        }).catch(error => {
          this.handleError('Es ist ein Fehler beim Abrufen der Wissensdatenbank aufgetreten.')
        })
      },
      loadCategories() {
        axios.get('/backend/api/v1/helpdesk/knowledge').then(response => {
          if (response.data.success) {
            this.categories = response.data.data
            if (this.categories) {
              this.categories = this.categories.sort((c1, c2) => {
                return c1.sortIndex - c2.sortIndex
              })
            }
          }
        }).catch(error => {
          this.$emit('error')
        })
      },
      removePage() {
        let confirmResult = confirm('Möchten Sie diese Seite wirklich löschen?')
        if (!confirmResult) {
          return
        }

        let id = this.query('page')
        axios.post('/backend/api/v1/helpdesk/pages/' + id + '/remove').then(response => {
          if (response.data.success) {
            this.handleSuccess('Diese Seite wurde erfolgreich gelöscht.')
            window.location = '/help/knowledge?page=1'
          } else {
            this.handleError('Es ist ein Fehler aufgetreten: ' + response.data.error)
          }
        }).catch(error => {
          this.handleError('Es ist ein unbekannter Fehler aufgetreten.')
        })
      },
      updatePage() {
        this.workMode = false

        let id = this.query('page')
        axios.post('/backend/api/v1/helpdesk/pages/' + id + '/update', this.currentPage).then(response => {
          if (response.data.success) {
            this.handleSuccess('Diese Seite wurde erfolgreich speichert.')
            this.loadPage()
            this.loadCategories()
          } else {
            this.handleError('Die Seite konnte nicht gespeichert werden.')
          }
        }).catch(error => {
          this.handleError('Es ist ein unbekannter Fehler aufgetreten.')
        })
      }
    },
    created() {
      this.loadCategories()
      this.loadPage()
    }
  }

</script>

<style lang="scss" scoped>
  #app {
    .knowledge-container {
      display: flex;
      width: 100%;

      .content {
        width: 76%;
        margin-right: 1%;
        background: white;
      }

      .sub-navigation {
        width: 23%;
        background: white;
      }
    }
  }
</style>

<style lang="scss">
  .toolbar__content,
  .toolbar__extension {
    background: #3d3d53;
  }
</style>
