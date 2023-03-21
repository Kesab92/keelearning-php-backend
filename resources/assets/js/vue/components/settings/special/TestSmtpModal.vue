<template>
  <div class="text-xs-center">
    <v-dialog v-model="isOpen" width="600">
      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>
          SMTP Einstellungen testen
        </v-card-title>
        <v-card-text v-if="isLoading">
          <v-progress-circular
            indeterminate
            class="mr-2" /> Testemail wird versendet...
        </v-card-text>
        <v-card-text v-else-if="success">
          <v-layout align-center row>
            <v-flex shrink class="mr-2">
              <v-icon color="green">done</v-icon>
            </v-flex>
            <v-flex>
              Die E-Mail wurde erfolgreich versendet. Bitte prüfen Sie, ob Sie die E-Mail auch empfangen haben.
            </v-flex>
          </v-layout>
        </v-card-text>
        <v-card-text v-else-if="error">
          <v-layout align-center row>
            <v-flex shrink class="mr-2">
              <v-icon color="red">error</v-icon>
            </v-flex>
            <v-flex>
              Die E-Mail konnte nicht versendet werden. Bitte prüfen Sie die SMTP Einstellungen.
            </v-flex>
          </v-layout>
        </v-card-text>
        <v-card-text v-else>
          <p>Um ihre Einstellungen zu testen, füllen Sie bitte das folgende E-Mail-Adressfeld aus. Bei erfolgreicher Konfiguration erhalten Sie eine E-Mail und können somit sicher sein, dass die Konfiguration erfolgreich war.</p>
          <v-text-field
            label="Ihre E-Mail Adresse"
            hide-details
            outline
            type="mail"
            v-model="email"/>
        </v-card-text>
        <v-card-actions>
          <v-btn
            v-if="!isLoading && !success && !error"
            color="primary"
            @click="startTest">
            Test E-Mail versenden
          </v-btn>
          <v-spacer />
          <v-btn
            color="secondary"
            @click="isOpen = false">
            Schließen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  export default {
    props: ['smtpSettings'],
    data() {
      return {
        isOpen: false,
        isLoading: true,
        email: null,
        success: false,
        error: false,
      }
    },
    methods: {
      open() {
        this.isLoading = false
        this.success = false
        this.error = false
        this.isOpen = true
      },
      startTest() {
        this.isLoading = true
        axios.post('/backend/api/v1/settings/test-smtp', {
          ...this.smtpSettings,
          email: this.email,
        })
        .then((response) => {
          if(response.data.success === true) {
            this.success = true
          } else {
            this.error = true
          }
        })
        .catch(() => {
          this.error = true
        })
        .finally(() => {
          this.isLoading = false
        })
      },
    },
  }
</script>
