<template>
  <div v-if="newsData">
    <NewsToolbar
      :news-data="newsData"
      :is-saving="isSaving"
      @save="save"
      @publishNow="publishNow"
    />
    <div class="pa-4">
      <div class="subheading">Coverbild</div>
      <ImageUploader
        :current-image="newsData.cover_image_url"
        name="Coverbild"
        :url="`/backend/api/v1/news/${newsData.id}/cover`"
        width="300px"
        height="180px"
        @newImage="handleNewImage"
      />
    </div>
  </div>
</template>

<script>
import NewsToolbar from "./NewsToolbar"
import ImageUploader from "../partials/global/ImageUploader"
import moment from "moment";
export default {
  props: ['newsEntry'],
  data() {
    return {
      newsData: null,
      isSaving: false,
    }
  },
  watch: {
    newsEntry: {
      handler() {
        if(!this.newsEntry) {
          return
        }
        this.newsData = JSON.parse(JSON.stringify(this.newsEntry))
      },
      immediate: true,
    },
  },
  methods: {
    handleNewImage(image) {
      this.newsData.cover_image_url = image
    },
    async save() {
      this.isSaving = true
      await this.$store.dispatch('news/saveNewsEntry', {
        id: this.newsData.id,
        cover_image_url: this.newsData.cover_image_url,
        published_at: this.newsData.published_at,
      })
      this.isSaving = false
    },
    publishNow() {
      this.newsData.published_at = moment(Date.now()).format('YYYY-MM-DD')
      this.save()
    },
  },
  components: {
    NewsToolbar,
    ImageUploader,
  },
}
</script>
