<template>
  <div>
    <div class="c-moduleIntro">
      <h1 class="c-moduleIntro__heading">
        Webinare
      </h1>
      <div class="c-moduleIntro__description">
        Erstellen Sie ein Webinar und laden Sie interne und externe Benutzer ein.
      </div>
      <div class="c-moduleIntro__links">
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233363-webinar-adminseite"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung Adminseite
        </v-btn>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233372-webinar-app"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung App
        </v-btn>
      </div>
    </div>
    <v-snackbar
      :top="true"
      color="success"
      v-model="deleteResponse"
    >
      Das Webinar wurde gelöscht.
    </v-snackbar>
    <v-card>
      <v-card-title
        primary-title
        class="pb-0"
      >
        <div class="headline">
          Webinare
        </div>
        <v-spacer />
        <webinar-modal
          :open="modalOpen"
          :tags="tags"
          @delete="deleteWebinar"
          @setOpen="setModalOpen"
          @update="updateWebinar"
        />
      </v-card-title>
      <v-card-text class="pt-4">
        <v-progress-circular
          v-if="isLoading"
          indeterminate
          color="primary"
          class="mt-3"
        />
        <template v-else>
          <v-list
            v-if="webinars && webinars.length"
            two-line
            class="pt-0"
          >
            <webinar-entry
              v-for="webinar in webinars"
              :key="webinar.id"
              :webinar="webinar"
              :tags="tags"
              @delete="deleteWebinar"
              @update="updateWebinar"
            />
          </v-list>
          <v-alert
            v-else
            type="info"
            :value="true"
          >
            Noch keine Webinare vorhanden.
          </v-alert>
        </template>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import WebinarEntry from './partials/webinars/WebinarEntry'
import WebinarModal from './partials/webinars/WebinarModal'

export default {
  data() {
    return {
      deleteResponse: false,
      isLoading: false,
      modalOpen: false,
      tags: [],
      webinars: [],
    }
  },
  created() {
    this.loadWebinars()
  },
  methods: {
    deleteWebinar(webinarId) {
      let index = this.webinars.findIndex(w => w.id == webinarId)
      this.$delete(this.webinars, index)
      this.deleteResponse = true
    },
    loadWebinars() {
      if (this.isLoading) {
        return
      }
      this.isLoading = true
      axios.get('/backend/api/v1/webinars').then(response => {
        if (response.data.success) {
          this.tags = response.data.tags
          this.webinars = response.data.webinars
        }
        this.isLoading = false
      })
    },
    setModalOpen(open) {
      this.modalOpen = open
    },
    updateWebinar(webinar) {
      let index = this.webinars.findIndex(w => w.id == webinar.id)
      if (index === -1) {
        this.webinars.unshift(webinar)
      } else {
        this.$set(this.webinars, index, webinar)
      }
    },
  },
  components: {
    WebinarEntry,
    WebinarModal,
  },
}
</script>
