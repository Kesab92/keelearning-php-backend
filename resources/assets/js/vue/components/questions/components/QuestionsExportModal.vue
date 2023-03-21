<template>
  <v-dialog
    v-model="isOpen"
    max-width="600px">
    <v-card>
      <v-toolbar
        card
        dark
        color="primary">
        <v-btn
          icon
          dark
          @click.native="isOpen = false">
          <v-icon>close</v-icon>
        </v-btn>
        <v-toolbar-title>Fragen exportieren</v-toolbar-title>
        <v-spacer/>
      </v-toolbar>
      <v-card-text>
        <v-btn
          v-for="downloadLink in downloadLinks"
          :key="downloadLink.link"
          color="primary"
          :href="downloadLink.link"
          target="_blank"
          class="s-downloadButton"
          block
        >
          <template v-if="downloadLink.from === downloadLink.to">
            {{ questionsCount }} Fragen exportieren ({{ downloadLink.from.toUpperCase() }})
          </template>
          <template v-else>
            {{ questionsCount }} Fragen exportieren ({{ downloadLink.from.toUpperCase() }} / {{ downloadLink.to.toUpperCase() }})
          </template>
          <v-icon right>cloud_download</v-icon>
        </v-btn>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
  export default {
    props: {
      availableLanguages: {
        type: Array,
        required: true,
      },
      currentLanguage: {
        type: String,
        required: true,
      },
      query: {
        type: String,
        required: false,
        default: null,
      },
      selectedFilters: {
        type: Array,
        required: false,
        default: null,
      },
      category: {
        type: Number,
        required: false,
        default: null,
      },
      questionsCount: {
        type: Number,
        required: false,
        default: null,
      },
    },
    data() {
      return {
        isOpen: false,
      }
    },
    computed: {
      downloadLinks() {
        const links = []
        const settings = {
          query: this.query,
          selectedFilters: this.selectedFilters,
          category: this.category,
        }
        let query = Object.keys(settings).map(key => {
          if (!settings[key]) {
            return null
          }
          return `${encodeURIComponent(key)}=${encodeURIComponent(settings[key])}`
        }).filter(v => v !== null).join('&')
        this.availableLanguages.forEach(targetLanguage => {
          links.push({
            link: '/questions/download/from/' + this.currentLanguage + '/to/' + targetLanguage + '?' + query ,
            from: this.currentLanguage,
            to: targetLanguage,
          })
        })
        return links
      },
    },
    methods: {
      open() {
        this.isOpen = true
      },
    },
  }
</script>

<style lang="scss" scoped>
  .s-downloadButton {
    text-transform: none !important;
  }
</style>
