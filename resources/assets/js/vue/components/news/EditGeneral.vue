<template>
  <div v-if="newsData">
    <NewsToolbar
      :news-data="newsData"
      :is-saving="isSaving"
      @save="save"
      @publishNow="publishNow"
    />
    <div class="pa-4">
      <translated-input
        v-model="newsData.title"
        :translations="newsData.translations"
        attribute="title"
        class="mb-4"
        label="Titel"/>
      <tag-select
        v-model="newsData.tags"
        color="blue-grey lighten-2"
        label="Sichtbar für folgende User"
        multiple
        outline
        placeholder="Alle"
        :limitToTagRights="true"
      />
      <translated-input
        v-model="newsData.content"
        input-type="texteditor"
        :translations="newsData.translations"
        attribute="content"
        :height="600"
        label="Inhalt"/>
      <DatePicker
        v-model="newsData.active_until"
        label="Ablaufdatum"
        class="mt-4"
      />
      <DatePicker
        v-model="newsData.published_at"
        label="Veröffentlichung"
      />
      <SendNotification :news-entry="newsEntry"/>
    </div>
  </div>
</template>

<script>
import SendNotification from "./SendNotification"
import NewsToolbar from "./NewsToolbar"
import TagSelect from "../partials/global/TagSelect"
import DatePicker from "../partials/global/Datepicker"
import ImageUploader from "../partials/global/ImageUploader"
import ClickOutside from "vue-click-outside"
import {mapGetters} from "vuex";
import moment from 'moment'

export default {
  props: ["newsEntry"],
  data() {
    return {
      newsData: null,
      isSaving: false,
    }
  },
  computed: {
    ...mapGetters({
      isFullAdmin: 'app/isFullAdmin',
    }),
  },
  watch: {
    newsEntry: {
      handler() {
        this.newsData = JSON.parse(JSON.stringify(this.newsEntry))
      },
      immediate: true,
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      let tags = this.newsData.tags
      this.isSaving = true
      await this.$store.dispatch("news/saveNewsEntry", {
        id: this.newsData.id,
        title: this.newsData.title,
        content: this.newsData.content,
        active_until: this.newsData.active_until,
        published_at: this.newsData.published_at,
        tags,
      })
      if(tags.length === 0 && !this.isFullAdmin) {
        await this.$router.push({
          name: 'news.index',
        })
      }
      this.isSaving = false
    },
    publishNow() {
      this.newsData.published_at = moment(Date.now()).format('YYYY-MM-DD')
      this.save()
    },
  },
  components: {
    NewsToolbar,
    SendNotification,
    ImageUploader,
    TagSelect,
    DatePicker,
  },
  directives: {
    ClickOutside,
  },
}
</script>
