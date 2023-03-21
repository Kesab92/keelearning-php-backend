<template>
    <div>
        <v-dialog v-model="active" width="500">
            <span slot="activator">
                <v-btn color="error">
                    <v-icon>thumb_down</v-icon>
                </v-btn>
            </span>
            <v-card>
                <v-card-title class="headline">Feedback</v-card-title>
                <v-card-text>
                    Beschreiben Sie bitte in dem folgenden Textfeld Ihr Anliegen. Wir melden uns bei Ihnen,
                    um das Anliegen mit Ihnen zu klären.

                    <v-textarea
                        solo
                        :disabled="loading"
                        label="Nachricht"
                        v-model="text"
                        placeholder="Ihre Nachricht an uns" />
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="red"
                        flat="flat"
                        @click="active = false">
                        Abbrechen
                    </v-btn>

                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="green darken-1"
                        flat="flat"
                        @click="send">
                        Senden
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
      props: {
        pageId: {
          type: Number,
          required: true
        }
      },
      data() {
        return {
          loading: false,
          active: false,
          text: null
        }
      },
      methods: {
        send() {
          if (!this.text) {
            return
          }

          this.loading = true
          axios.post('/backend/api/v1/helpdesk/pages/' + this.pageId + '/sendFeedback', { text: this.text }).then(response => {
            if (response.data.success) {
              this.$emit('update', true)
              this.active = false
              this.text = null
            } else {
              this.$emit('update', false)
            }
            this.loading = false
          }).catch(error => {
            this.$emit('update', false)
          })
        }
      }
    }
</script>