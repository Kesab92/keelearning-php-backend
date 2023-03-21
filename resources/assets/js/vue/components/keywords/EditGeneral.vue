<template>
  <div v-if="keywordData">
    <KeywordToolbar
      :keyword-data="keywordData"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <translated-input
        v-model="keywordData.name"
        :translations="keywordData.translations"
        attribute="name"
        class="mb-4"
        label="Name"/>
      <content-category-select
        v-model="keywordData.categories"
        label="Kategorie"
        :type="$constants.CONTENT_CATEGORIES.TYPE_KEYWORDS"
        multiple />
      <translated-input
        v-model="keywordData.description"
        input-type="texteditor"
        :translations="keywordData.translations"
        attribute="description"
        label="Erläuterung"/>
    </div>
  </div>
</template>

<script>
import KeywordToolbar from "./KeywordToolbar"
import ClickOutside from "vue-click-outside"

export default {
  props: ["keyword"],
  data() {
    return {
      keywordData: null,
      isSaving: false,
    }
  },
  watch: {
    keyword: {
      handler() {
        this.keywordData = JSON.parse(JSON.stringify(this.keyword))
      },
      immediate: true,
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      await this.$store.dispatch("keywords/saveKeyword", {
        id: this.keywordData.id,
        name: this.keywordData.name,
        description: this.keywordData.description,
        categories: this.keywordData.categories,
      })
      this.isSaving = false
    },
  },
  components: {
    KeywordToolbar,
  },
  directives: {
    ClickOutside,
  },
}
</script>
