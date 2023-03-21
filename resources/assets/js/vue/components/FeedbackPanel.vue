<template>
    <div>
        <v-snackbar v-model="snackbar" :top="true" color="success">Danke. Ihr Feedback ist bei uns angekommen.</v-snackbar>
        <v-snackbar v-model="error" :top="true" color="error">Es ist ein unbekannter Fehler aufgetreten.</v-snackbar>
        <div class="feedbackPanel">
            <h5>Hat Ihnen dieser Artikel weitergeholfen?</h5>
            <div class="counter" v-if="superadmin">
                <span v-if="success">{{(page.feedbackCount + 1) }}</span>
                <span v-else>{{ page.feedbackCount }}</span>
                Benutzer finden diesen Artikel hilfreich
            </div>
            <div v-if="!superadmin && (!page.hasAuthenticatedUserFeedback && !success)" class="button-group">
                <v-btn
                    @click="positiveFeedback"
                    color="success">
                    <v-icon>thumb_up</v-icon>
                </v-btn>
                <FeedbackModal
                    :pageId="page.id"
                    @update="showSnachkbar"
                />
            </div>
            <div v-if="page.hasAuthenticatedUserFeedback || success">
                Sie finden den Artikel bereits hilfreich.
            </div>
        </div>
    </div>
</template>

<script>
    import FeedbackModal from './partials/knowledge-modal/FeedbackModal'

    export default {
      components: {
        FeedbackModal
      },
      methods: {
        positiveFeedback() {
          axios.post('/backend/api/v1/helpdesk/pages/' + this.page.id + '/feedback').then(response => {
            if (response.data.success) {
              this.success = true
              this.showSnachkbar(true)
            } else {
              this.showSnachkbar(false)
            }
          }).catch(error => {
            this.showSnachkbar(false)
          })
        },
        showSnachkbar(success) {
          success ? this.snackbar = true : this.error = true
        }
      },
      props: {
        page: {
          type: Object,
          required: true
        },
        superadmin: {
          type: Boolean,
          required: true
        }
      },
      data() {
        return {
          snackbar: false,
          success: false,
          error: false
        }
      }
    }
</script>

<style lang="scss" scoped>
    .feedbackPanel {
        margin: 20px 0;

        .button-group {
            display: flex;
            flex-direction: row;
        }
    }

    #app h5 {
        margin-bottom: 10px;
    }

    #app .counter {
        margin-bottom: 10px;
    }

    #app .v-card__actions .v-btn, #app .v-card__actions > * {
        margin-right: 20px;
    }
</style>