<template>
  <div>
    <div class="headline mb-1">SMTP Einstellungen</div>
    <div class="body-1">Stellen Sie hier Ihre Daten des E-Mail-Ausgangsservers ein. Wenn Sie die Felder leer lassen, wird der Keeunit E-Mail Versand genutzt. Die Mails verwenden dann den Absender app@keelearning.de.</div>
    <div class="mb-2">
      <v-btn
        flat
        color="primary"
        small
        href="https://helpdesk.keelearning.de/de/articles/5707612-eigenen-smtp-server-verwenden"
        target="_blank"
      >
        <v-icon
          small
          class="mr-1">
          help
        </v-icon>
        Anleitung öffnen
      </v-btn>
    </div>
    <v-alert
      :value="true"
      color="warning"
      icon="priority_high"
      outline
      class="mb-4"
    >
      Um einen reibungslosen Ablauf zu ermöglichen, stellen Sie bitte in Zusammenarbeit mit Ihrer IT Abteilung sicher, dass Ihr individueller E-Mail Server ausreichend viele E-Mails versenden kann.<br>
      Wenn z.B. 1000 Benutzer die keelearning-App verwenden, sollten Sie sicher stellen, dass der Server zu Spitzenzeiten mindestens die doppelte Anzahl, also 2000 Emails, pro Stunde versenden kann.
    </v-alert>
    <v-form v-model="formIsValid">
      <v-container
        grid-list-md
        class="pa-0 mb-4">
        <v-layout row>
          <v-flex
            md6
            xs12>
            <v-text-field
              label="Host"
              outline
              hide-details
              v-model="config.smtp_host"/>
          </v-flex>
          <v-flex
            md3
            xs6>
            <v-text-field
              label="Port"
              outline
              hide-details
              type="number"
              v-model="config.smtp_port"/>
          </v-flex>
          <v-flex
            md3
            xs6>
            <v-select
              :items="encryptionOptions"
              v-model="config.smtp_encryption"
              hide-details
              label="Verschlüsselung"
              item-value="key"
              item-text="label"
              outline />
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex
            xs12
            md6>
            <v-text-field
              label="Benutzername"
              hide-details
              outline
              v-model="config.smtp_username"/>
          </v-flex>
          <v-flex
            xs12
            md6>
            <v-text-field
              label="Passwort"
              hide-details
              outline
              type="password"
              v-model="config.smtp_password"/>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex xs12>
            <v-text-field
              label="Absender E-Mail Adresse"
              hide-details
              outline
              type="mail"
              v-model="config.smtp_email"/>
          </v-flex>
        </v-layout>
      </v-container>
      <v-alert
        :value="isInvalidPort"
        color="warning"
        icon="priority_high"
        outline
        class="mb-4"
      >
        Aus technischen Gründen ist der Versand über Port 25 nicht möglich.
      </v-alert>
      <v-layout row>
        <v-flex>
          <v-btn
            color="primary"
            class="ml-0"
            :loading="isSaving"
            :disabled="isSaving || isInvalidPort"
            @click="updateAppConfigItems">
            SMTP Einstellungen speichern
            <template
              v-if="savingSuccess"
              v-slot:loader>
              <v-icon light>done</v-icon>
            </template>
          </v-btn>
        </v-flex>
        <v-spacer />
        <v-flex shrink>
          <v-btn
            class="mr-0"
            :disabled="isInvalidPort"
            @click="testSettings">
            SMTP Einstellungen testen
          </v-btn>
        </v-flex>
      </v-layout>
    </v-form>
    <TestSmtpModal
      :smtp-settings="config"
      ref="testSmtpModal" />
  </div>
</template>

<script>
  import AreaMixin from "./areaMixin"
  import TestSmtpModal from "../special/TestSmtpModal"

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        isTesting: false,
        config: {
          smtp_host: null,
          smtp_port: null,
          smtp_username: null,
          smtp_password: null,
          smtp_email: null,
          smtp_encryption: null,
        },
        encryptionOptions: [
          {
            key: 'tls',
            label: 'TLS',
          },
          {
            key: 'ssl',
            label: 'SSL',
          },
        ],
      }
    },
    computed: {
      isInvalidPort() {
        return this.config.smtp_port == 25
      },
    },
    methods: {
      testSettings() {
        this.$refs.testSmtpModal.open()
      },
    },
    components: {
      TestSmtpModal,
    },
  }
</script>
