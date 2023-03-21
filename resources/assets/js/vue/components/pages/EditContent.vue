<template>
  <div v-if="pageEditable">
    <PageToolbar
      :page="pageEditable"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <translated-input
        v-model="pageEditable.content"
        input-type="texteditor"
        :translations="pageEditable.translations"
        attribute="content"
        :height="700"
        label="Inhalt"/>
    </div>
  </div>
</template>

<script>
import PageToolbar from "./PageToolbar"
import ClickOutside from "vue-click-outside"

export default {
  props: ["page"],
  data() {
    return {
      pageEditable: null,
      isSaving: false,
    }
  },
  watch: {
    page: {
      handler() {
        this.pageEditable = JSON.parse(JSON.stringify(this.page))
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
      try{
        await this.$store.dispatch("pages/savePage", {
          id: this.pageEditable.id,
          content: this.pageEditable.content,
        })
      } catch (e) {
        alert('Die Seite konnte leider nicht gespeichert werden.')
      }
      this.isSaving = false
    },
  },
  components: {
    PageToolbar,
  },
  directives: {
    ClickOutside,
  },
}
</script>
