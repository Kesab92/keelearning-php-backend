<template>
  <div v-if="tagData">
    <TagToolbar
      :tag-data="tagData"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <v-text-field
        v-model="tagData.label"
        browser-autocomplete="chrome-off"
        label="Name"
        outline />

      <v-select
        v-if="!appSettings.hide_tag_groups || appSettings.hide_tag_groups == 0"
        v-model="tagData.tag_group_id"
        :items="tagGroups"
        class="mb-4"
        hide-details
        label="TAG-Gruppe"
        item-value="id"
        item-text="name"
        clearable
        outline />

      <content-category-select
        v-model="tagData.contentcategories"
        label="Kategorie"
        :type="$constants.CONTENT_CATEGORIES.TYPE_TAGS"
        multiple />

      <v-switch
        label="Quiz-Battle nur gegen User mit diesem TAG erlauben"
        hint='Sie "schließen" hiermit die User innerhalb ihres TAGs ein: User können jetzt nur gegen andere User antreten, die den gleichen TAG besitzen.'
        persistent-hint
        v-model="tagData.exclusive"
      />
      <v-switch
        label="User nicht in der Quiz-Statistik der App aufführen"
        v-model="tagData.hideHighscore"
      />
    </div>
  </div>
</template>

<script>
import TagToolbar from "./TagToolbar"
import {mapGetters} from "vuex";

export default {
  props: ["tag"],
  data() {
    return {
      tagData: null,
      isSaving: false,
      tagGroups: [],
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
  },
  watch: {
    'appSettings.hide_tag_groups': {
      handler() {
        this.loadTagGroups()
      },
      immediate: true,
    },
    tag: {
      handler() {
        this.tagData = JSON.parse(JSON.stringify(this.tag))
      },
      immediate: true,
    },
    'tagData.tag_group_id'() {
      if (typeof this.tagData.tag_group_id === "undefined") {
        this.tagData.tag_group_id = null
      }
    }
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      await this.$store.dispatch("tags/saveTag", {
        id: this.tagData.id,
        label: this.tagData.label,
        tag_group_id: this.tagData.tag_group_id,
        exclusive: this.tagData.exclusive,
        hideHighscore: this.tagData.hideHighscore,
        contentcategories: this.tagData.contentcategories,
      })
      this.isSaving = false
    },
    loadTagGroups() {
      if (this.appSettings.hide_tag_groups != '0') {
        return
      }
      axios.get("/backend/api/v1/tag-groups/get-tag-groups").then(response => {
        this.tagGroups = response.data.tagGroups
      })
    },
  },
  components: {
    TagToolbar,
  },
}
</script>
