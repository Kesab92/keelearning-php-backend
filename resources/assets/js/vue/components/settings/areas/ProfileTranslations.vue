<template>
  <div>
    <div class="headline mb-1">Übersetzungen</div>
    <div class="body-1 mb-2">Hier können Übersetzungen aus dem Frontend überschrieben werden.</div>
    <v-progress-circular
      v-if="isLoading"
      indeterminate
      color="primary"
    />
    <template v-else>
      <v-layout
        v-for="translation in translations"
        :key="translation.id"
        class="mb-2"
        align-center
        row>
        <v-flex
          md4
          xs12>
          <v-text-field
            class="mr-2"
            label="key"
            outline
            hide-details
            v-model="translation.key"/>
        </v-flex>
        <v-flex
          md7
          xs12>
          <v-text-field
            label="Übersetzung"
            outline
            hide-details
            v-model="translation.content"/>
        </v-flex>
        <v-flex>
          <v-btn
            icon
            @click="deleteTranslation(translation)"
            class="my-0"
            outline
            color="error">
            <v-icon>delete</v-icon>
          </v-btn>
        </v-flex>
      </v-layout>
      <v-layout
        row
        class="mt-4">
        <v-flex>
          <v-btn
            color="primary"
            class="ml-0"
            :loading="isSaving"
            :disabled="isSaving"
            @click="saveTranslations">
            Übersetzungen speichern
            <template
              v-if="savingSuccess"
              v-slot:loader>
              <v-icon light>done</v-icon>
            </template>
          </v-btn>
        </v-flex>
      </v-layout>
      <v-list
        class="mt-4"
        subheader
        two-line
        dense
      >
        <v-subheader>Viel genutzte keys</v-subheader>

        <v-list-tile
          v-for="entry in commonKeys"
          :key="entry.key">
          <v-list-tile-content>
            <v-list-tile-title>{{ entry.key }}</v-list-tile-title>
            <v-list-tile-sub-title>{{ entry.description }}</v-list-tile-sub-title>
          </v-list-tile-content>
        </v-list-tile>
      </v-list>
    </template>
  </div>
</template>

<script>

export default {
  data() {
    return {
      isLoading: true,
      isTesting: false,
      isSaving: false,
      savingSuccess: false,
      translations: null,
      commonKeys: [
        {
          key: 'auth.slogan',
          description: 'Der Slogan direkt unter dem Bild auf der Login Seite. Hier können html tags verwendet werden.',
        },
        {
          key: 'auth.words',
          description: 'Die drei "mood words" unterhalb vom Slogan',
        },
      ]
    }
  },
  created() {
    this.isLoading = true
    this.loadTranslations().then(() => {
      this.isLoading = false
    })
  },
  watch: {
    translations: {
      handler() {
        if(!this.translations) {
          return
        }
        if(!this.translations.find(translation => !translation.key)) {
          this.translations.push({
            id: (new Date().getTime()) * -1,
            key: '',
            content: '',
          })
        }
      },
      deep: true,
    },
  },
  methods: {
    loadTranslations() {
      return axios.get('/backend/api/v1/settings/translations/' + this.$route.params.profileId).then((response) => {
        this.translations = response.data.translations
      })
    },
    saveTranslations() {
      this.savingSuccess = false
      this.isSaving = true
      axios.post('/backend/api/v1/settings/translations/' + this.$route.params.profileId, {
        translations: this.translations,
      }).catch(() => {
        alert('Die Übersetzungen konnten leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
        this.isSaving = false
      }).then(() => {
        this.loadTranslations().then(() => {
          this.savingSuccess = true
          setTimeout(() => (this.isSaving = false), 1000)
        })
      })
    },
    deleteTranslation(translation) {
      if(!confirm('Möchten Sie die individuelle Übersetzung für "' + translation.key + '" entfernen?')) {
        return
      }
      let idx = this.translations.findIndex(t => t.id === translation.id)
      this.translations.splice(idx, 1)
    },
  },
}
</script>
