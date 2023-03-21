<template>
    <div class="text-xs-center">
        <v-snackbar v-model="success" :top="true" color="success">Die TAG-Gruppe wurde erfolgreich gespeichert.</v-snackbar>
        <v-snackbar v-model="error" :top="true" color="error">{{ message }}</v-snackbar>
        <v-dialog v-model="dialog" width="500">
            <v-btn slot="activator" color="primary" dark>
                {{ buttonTitle }}
            </v-btn>

            <v-card>
                <form @submit.prevent="save">
                    <v-card-title class="headline lighten-2" primary-title>
                        <template v-if="tagGroup.created">
                            TAG-Gruppe hinzufügen
                        </template>
                        <template v-else>
                            TAG-Gruppe bearbeiten
                        </template>
                    </v-card-title>

                    <v-card-text>
                        <v-text-field
                            label="Name"
                            :rules="rules"
                            v-model="tagGroup.name"
                            required
                        />
                        <v-switch
                            label="bei Registrierung auswählbar"
                            v-model="tagGroup.signup_selectable"
                        />
                        <v-switch
                            :disabled="!tagGroup.signup_selectable"
                            label="Auswahl verpflichtend"
                            v-model="tagGroup.signup_required"
                        />
                        <v-switch
                            label="User mit diesem TAG im Highscore anzeigen"
                            v-model="tagGroup.show_highscore_tag"
                        />
                    </v-card-text>

                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            :loading="isLoading"
                            :disabled="isLoading"
                            type="submit"
                            color="success">
                            Speichern
                        </v-btn>
                        <v-btn
                            type="button"
                            :loading="isLoading"
                            :disabled="isLoading"
                            color="error"
                            @click.prevent="dialog = false">
                            Abbrechen
                        </v-btn>
                    </v-card-actions>
                </form>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
      props: {
        buttonTitle: {
          type: String,
          required: true
        },
        tagGroup: {
          type: Object,
          required: false,
          default() {
            return {
              created: true
            }
          }
        }
      },
      data() {
        return {
          dialog: false,
          success: false,
          error: false,
          message: null,
          isLoading: false,
          rules: [
            v => !!v || 'Der Name ist ein Pflichtfeld',
          ]
        }
      },
      methods: {
        save() {
          this.isLoading = true
          let apiCallURL = this.tagGroup.created
            ? '/backend/api/v1/tag-groups'
            : '/backend/api/v1/tag-groups/' + this.tagGroup.id

          axios.post(apiCallURL, this.tagGroup)
            .then(response => {
              if (response.data.success) {
                this.success = true
                this.dialog = false
                this.$emit('success')
              }
              this.isLoading = false
            })
            .catch(error => {
              this.message = error
              this.error = true
            })
        }
      }
    }
</script>
