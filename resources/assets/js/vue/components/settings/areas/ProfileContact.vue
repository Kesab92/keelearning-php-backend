<template>
  <div>
    <div class="headline mb-5">Kontakt- und Rechtliche Informationen</div>
    <v-form v-model="formIsValid">
      <v-layout
        row>
        <v-flex
          md6
          xs12
          class="pr-3">
          <v-text-field
            label="Telefon"
            outline
            hint="Kontakt Telefonnummer die Benutzern angezeigt wird"
            v-model="config.contact_phone"/>

          <v-text-field
            label="Email Benachrichtigungen (Kommagetrennt)"
            placeholder="email1@mail.com,email2@mail.com"
            outline
            hint="An diese Adressen werden App Feedback und vorgeschlagene Fragen geschickt"
            v-model="config.notification_mails"/>

          <PageSelect
            v-if="myRights['pages-edit']"
            v-model="config.tos_id"
            :show-only-visible="true"
            :for-tags="profileTagIds"
            color="blue-grey lighten-2"
            label="Seite mit Nutzungsbedingungen"
            hint="Die Seite wird den Benutzern nach dem ersten Login zum Bestätigen angezeigt"
            outline
            persistent-hint />

        </v-flex>
        <v-flex
          md6
          xs12>
          <v-text-field
            label="E-Mail"
            outline
            hint="Kontakt E-Mail die Benutzern angezeigt wird"
            v-model="config.contact_email" />
          <v-textarea
            outline
            auto-grow
            v-model="config.email_terms"
            label="Teilnahme- und Nutzungsbedingungen im E-Mail Footer" />
        </v-flex>
      </v-layout>
      <v-btn
        color="primary"
        class="ml-0"
        :loading="isSaving"
        :disabled="isSaving"
        @click="updateAppConfigItems">
        Speichern
        <template
          v-if="savingSuccess"
          v-slot:loader>
          <v-icon light>done</v-icon>
        </template>
      </v-btn>
    </v-form>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import AreaMixin from "./areaMixin"
  import PageSelect from "../../partials/global/PageSelect"

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        config: {
          contact_phone: null,
          contact_email: null,
          notification_mails: null,
          tos_id: null,
          email_terms: null,
        },
      }
    },
    created() {
      if(this.profileSettings.tos_id.value) {
        this.config.tos_id = parseInt(this.profileSettings.tos_id.value, 10)
      }
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      currentProfile() {
        return this.profiles.find(profile => profile.id === this.profileId)
      },
      profileTagIds() {
        return this.currentProfile.tags.map(tag => tag.id)
      },
    },
    components: {
      PageSelect,
    },
  }
</script>
