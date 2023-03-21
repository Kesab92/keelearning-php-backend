<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        Mediathek
      </template>
      <template v-slot:description>
        Hinterlegen Sie Dateien in der App.
      </template>
      <template v-slot:links>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233313-uber-die-mediathek"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung öffnen
        </v-btn>
      </template>
    </ModuleIntro>
    <v-layout
      row
      align-center
      class="s-header">
      <PathDisplay v-if="!showSearchResult" />
      <v-spacer />
      <v-text-field
        class="mr-4"
        append-icon="search"
        clearable
        placeholder="Name"
        single-line
        v-model="search"/>
      <v-flex shrink>
        <v-menu
          :nudge-top="40"
          offset-y>
          <v-btn
            slot="activator"
            :disabled="showSearchResult"
            outline
            color="primary">
            <v-icon left>add</v-icon>
            Neuer Inhalt
          </v-btn>
          <v-list v-if="!showSearchResult">
            <v-list-tile
              @click="openAddMaterialModal">
              <v-list-tile-title>Neue Datei</v-list-tile-title>
            </v-list-tile>
            <v-list-tile
              @click="openAddFolderModal">
              <v-list-tile-title>Neuer Ordner</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </v-flex>
    </v-layout>
    <FolderContents v-if="!showSearchResult" />
    <SearchResult v-if="showSearchResult" />
    <LearningmaterialSidebar />
    <LearningmaterialFolderSidebar />
    <AddFolderModal v-model="addFolderModalOpen" />
    <AddMaterialModal v-model="addMaterialModalOpen" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import FolderContents from "./FolderContents"
import PathDisplay from "./PathDisplay"
import LearningmaterialSidebar from "./LearningmaterialSidebar"
import LearningmaterialFolderSidebar from "./LearningmaterialFolderSidebar"
import AddFolderModal from "./create-modals/AddFolderModal"
import AddMaterialModal from "./create-modals/AddMaterialModal"
import SearchResult from "./SearchResult"
import ModuleIntro from "../partials/global/ModuleIntro"
export default {
  props: ['has_candy_frontend'],
  created() {
    this.$store.dispatch('learningmaterials/updateLearningmaterials')
    this.$store.dispatch('tags/updateTags')
  },
  data() {
    return {
      addMaterialModalOpen: false,
      addFolderModalOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      folderId: 'learningmaterials/folderId',
    }),
    search: {
      get() {
        return this.$store.state.learningmaterials.search
      },
      set(data) {
        this.$store.commit('learningmaterials/setSearch', data)
      },
    },
    showSearchResult() {
      return !!this.search
    },
  },
  methods: {
    openAddMaterialModal() {
      if(!this.folderId) {
        alert('Im Hauptordner können keine Dateien angelegt werden. Bitte navigieren Sie erst in einen Ordner.')
        return
      }
      this.addMaterialModalOpen = true
    },
    openAddFolderModal() {
      if(this.folderId && this.has_candy_frontend !== '1') {
        alert('Das Anlegen von weiteren Unterordnern ist nur in Candy-Apps (neues Design) möglich.')
        return
      }
      this.addFolderModalOpen = true
    }
  },
  components: {
    AddMaterialModal,
    LearningmaterialFolderSidebar,
    LearningmaterialSidebar,
    PathDisplay,
    FolderContents,
    AddFolderModal,
    SearchResult,
    ModuleIntro,
  },
}
</script>

<style lang="scss" scoped>
.s-header {
  background: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}
</style>
