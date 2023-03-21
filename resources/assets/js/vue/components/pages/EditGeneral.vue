<template>
  <div v-if="pageEditable">
    <PageToolbar
      :page="pageEditable"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <translated-input
        v-model="pageEditable.title"
        :translations="pageEditable.translations"
        attribute="title"
        class="mb-4"
        label="Titel"/>

      <Toggle
        v-model="pageEditable.visible"
        label="Sichtbar" />

      <v-text-field
        v-if="pageEditable.public && !pageEditable.parent_id"
        v-model="pageEditable.public_link"
        readonly
        browser-autocomplete="chrome-off"
        label="Öffentlicher Link"
        outline />

      <Toggle
        v-if="!pageEditable.parent_id"
        v-model="pageEditable.public"
        label="Öffentlich" />

      <Toggle
        v-if="!pageEditable.parent_id"
        v-model="pageEditable.show_on_auth"
        label="Link auf Login Seiten anzeigen" />

      <Toggle
        v-if="!pageEditable.parent_id"
        v-model="pageEditable.show_in_footer"
        label="Link in der Fußzeile der App anzeigen" />
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
      if(this.pageEditable.visible && !this.pageEditable.content) {
        alert('Bitte geben Sie erst einen Seiteninhalt ein, bevor Sie die Seite sichtbar schalten.')
        return
      }
      this.isSaving = true
      try{
        await this.$store.dispatch("pages/savePage", {
          id: this.pageEditable.id,
          title: this.pageEditable.title,
          visible: this.pageEditable.visible,
          public: this.pageEditable.public,
          show_on_auth: this.pageEditable.show_on_auth,
          show_in_footer: this.pageEditable.show_in_footer,
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
