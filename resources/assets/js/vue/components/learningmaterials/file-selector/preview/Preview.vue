<template>
  <div>
    <component
      v-if="previewComponent"
      :is="previewComponent"
      :material-data="translation"
      ref="preview" />
    <template v-if="fileType !== 'azure_video'">
      <a
        v-if="translation.file_url"
        :href="translation.file_url"
        target="_blank">
        {{ fileName }}
      </a>
      <template v-if="translation.file_size_kb">
        ({{ translation.file_size_kb | fileSizeKB }})
      </template>
    </template>
  </div>
</template>

<script>
import AzureVideo from "./AzureVideo"
import MiscFile from "./MiscFile"
import Audio from "./Audio"
import Image from "./Image"
import YouTube from "./YouTube"
import Link from "./Link"
export default {
  props: ['translation', 'fileType'],
  computed: {
    previewComponent() {
      if(!this.fileType) {
        return null
      }
      switch(this.fileType) {
        case 'azure_video':
          return AzureVideo
        case 'misc':
          return MiscFile
        case 'audio':
          return Audio
        case 'image':
          return Image
        case 'youtube':
          return YouTube
        case 'link':
          return Link
      }
    },
    fileName() {
      if(!this.translation || !this.translation.file) {
        return ''
      }
      const parts = this.translation.file.split('/').filter(part => !!part)
      return parts[parts.length - 1]
    },
  },
  methods: {
    refresh() {
      if (!this.fileType || this.fileType != 'azure_video') {
        return
      }
      this.$refs.preview.refresh()
    },
  },
}
</script>
