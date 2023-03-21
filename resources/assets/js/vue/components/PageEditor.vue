<template>
  <div class="pageEditor">
    <v-snackbar
      v-model="snackbar"
      :top="true"
      color="error">Es ist ein Fehler beim Hochladen des Bildes aufgetreten.</v-snackbar>
    <div class="card-container">
      <div
        class="progress-container"
        v-if="currentPage === null">
        <v-progress-circular indeterminate/>
      </div>
      <div v-else>
        <div v-if="workMode">
          <v-text-field
            label="Titel"
            v-model="currentPage.title" />

          <v-select
            v-if="hasCategories"
            item-text="text"
            item-value="value"
            label="Kategorie"
            :items="categoryValues"
            v-model="currentPage.category"
          />
        </div>
        <h2
          class="page-title"
          v-else-if="displayTitle">
          {{ currentPage.title }}
        </h2>

        <Editor
          v-if="workMode"
          :init="tinyMCEEditorOptions"
          v-model="currentPage.content"
        />
        <div
          v-html="currentPage.content"
          v-else
        />
      </div>
    </div>

    <v-card-actions class="buttons">
      <FeedbackPanel
        :page="currentPage"
        :superadmin="superadmin"
      />
      <v-spacer/>
      <div
        class="button-group"
        v-if="superadmin">
        <v-btn
          flat
          v-if="!workMode"
          color="success"
          @click="workMode = true">
          Bearbeiten
        </v-btn>
        <v-btn
          flat
          v-if="workMode"
          color="success"
          @click="update">
          Speichern
        </v-btn>
        <v-btn
          flat
          color="error"
          @click="remove">
          Löschen
        </v-btn>
      </div>
    </v-card-actions>
  </div>
</template>

<script>
  import Editor from '@tinymce/tinymce-vue'
  import TinyMCEOptions from '../logic/tinyMCEOptions'
  import FeedbackPanel from './FeedbackPanel'

  export default {
    props: {
      superadmin: {
        type: Boolean,
        required: true
      },
      currentPage: {
        type: Object,
        required: true
      },
      displayTitle: {
        type: Boolean,
        required: false,
        'default': true
      },
      categories: {
        type: Array,
        required: false
      }
    },
    data() {
      return {
        snackbar: false,
        workMode: false,
      }
    },
    methods: {
      update() {
        this.workMode = false
        this.$emit('updatePage')
      },
      remove() {
        this.workMode = false
        this.$emit('removePage')
      }
    },
    computed: {
      tinyMCEEditorOptions() {
        const options = TinyMCEOptions()
        options.plugins = [
          'image',
          'link',
          'media',
          'lists',
        ]
        options.height = 800
        return options
      },
      categoryValues() {
        return this.categories.map(item => {
          return {
            text: item.name,
            value: item.id
          }
        })
      },
      hasCategories() {
        return this.currentPage && this.currentPage.category
      }
    },
    components: {
      FeedbackPanel,
      Editor
    }
  }
</script>

<style lang="scss" scoped>
  #app {
    .content {
      flex-direction: column;
    }

    .pageEditor {
      width: 100%;
    }

    .card-container {
      padding: 20px;
    }

    .page-title {
      font-weight: bold;
      padding-bottom: 20px;
    }

    .buttons {
      padding: 20px;
      position: relative;
      background: rgba(0, 0, 0, .06);
      border-top: 1px solid rgba(0, 0, 0, .07);

      .button-group {
        position: absolute;
        bottom: 10px;
        right: 10px;
      }
    }

  }
</style>
