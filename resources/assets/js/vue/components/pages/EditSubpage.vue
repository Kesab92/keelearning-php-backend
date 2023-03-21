<template>
  <div v-if="pageEditable && appSettings.has_subpages == 1 && !page.hasSubPages">
    <PageToolbar
      :page="pageEditable"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <p>Sie können eine Seite zu einer Sub-Seite erklären und mittels TAGs bestimmen, welchen Benutzergruppen die Sub-Seite statt der original Seite sehen sollen. Einer Sub-Seite muss mindestens ein TAG zugewiesen sein.</p>
      <page-select
        v-model="pageEditable.parent_id"
        color="blue-grey lighten-2"
        label="Sub-Seite von"
        outline
        placeholder="None"
        :invisible-items="[pageEditable.id]"
      />
      <tag-select
        v-if="pageEditable.parent_id || pageEditable.tags.length > 0"
        v-model="pageEditable.tags"
        color="blue-grey lighten-2"
        label="Überschreibt die Seite für folgende Benutzer"
        multiple
        outline
        placeholder="Bitte wählen"
        :limitToTagRights="true"
      />
    </div>
  </div>
</template>

<script>
import PageToolbar from "./PageToolbar"
import PageSelect from "../partials/global/PageSelect"
import TagSelect from "../partials/global/TagSelect"
import ClickOutside from "vue-click-outside"
import {mapGetters} from "vuex";

export default {
  props: ["page"],
  data() {
    return {
      pageEditable: null,
      isSaving: false,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
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
      if((!this.pageEditable.parent_id && this.pageEditable.tags.length > 0) || (this.pageEditable.parent_id && this.pageEditable.tags.length === 0)) {
        alert('Sub-Seiten müssen immer mindestens einen TAG zugewiesen haben.')
        return
      }
      this.isSaving = true
      let tags = this.pageEditable.tags
      try{
        await this.$store.dispatch("pages/savePage", {
          id: this.pageEditable.id,
          parent_id: this.pageEditable.parent_id,
          tags,
        })
      } catch (e) {
        alert('Die Seite konnte leider nicht gespeichert werden.')
      }
      this.isSaving = false
    },
  },
  components: {
    PageToolbar,
    PageSelect,
    TagSelect,
  },
}
</script>
