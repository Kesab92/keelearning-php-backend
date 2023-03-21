<template>
  <div v-if="categoryData">
    <details-sidebar-toolbar>
      <v-btn
        :loading="isSaving"
        color="primary"
        :disabled="readOnly"
        @click="save"
      >
        Speichern
      </v-btn>
      <v-spacer/>
      <v-btn
        :loading="isSaving"
        color="red"
        outline
        :disabled="readOnly"
        @click="deleteDialogOpen = true"
      >
        Löschen
      </v-btn>
    </details-sidebar-toolbar>

    <div class="pa-4">
      <translated-input
        v-model="categoryData.name"
        :translations="categoryData.translations"
        attribute="name"
        class="mb-4"
        :readOnly="readOnly"
        label="Name"/>

      <DeleteDialog
        v-model="deleteDialogOpen"
        v-if="!readOnly"
        :deletion-url="`/backend/api/v1/content-categories/${category.id}`"
        :dependency-url="`/backend/api/v1/content-categories/${category.id}/delete-information`"
        :entry-name="category.name"
        :redirect-url="afterDeletionRedirectURL"
        type-label="Kategorie"
        @deleted="handleCategoryDeleted"/>
    </div>
  </div>
</template>

<script>

import DeleteDialog from "../global/DeleteDialog"

export default {
  props: {
    category: {
      type: Object,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    readOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      categoryData: null,
      isSaving: false,
      deleteDialogOpen: false,
    }
  },
  watch: {
    category: {
      handler() {
        this.categoryData = JSON.parse(JSON.stringify(this.category))
      },
      immediate: true,
    },
  },
  computed: {
    afterDeletionRedirectURL() {
      if(this.type === this.$constants.CONTENT_CATEGORIES.TYPE_COURSES) {
        return "/courses#/courses/categories"
      }
      if(this.type === this.$constants.CONTENT_CATEGORIES.TYPE_KEYWORDS) {
        return "/keywords#/keywords/categories"
      }
      return '/'
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      await this.$store.dispatch("contentCategories/saveCategory", {
        id: this.categoryData.id,
        name: this.categoryData.name,
      }).catch(e => {
        alert('Die Kategorie konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isSaving = false
      })
    },
    handleCategoryDeleted() {
      this.$store.dispatch("contentCategories/updateCategories", this.type)
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>
