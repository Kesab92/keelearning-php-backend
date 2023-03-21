<template>
  <div>
    <component
      v-if="previewComponent"
      :is="previewComponent"
      :attachment="attachment" />
    <template v-if="fileType !== 'azure_video'">
      <a
        v-if="attachment.url"
        :href="attachment.url"
        target="_blank">
        {{ fileName }}
      </a>
      <template v-if="attachment.file_size_kb">
        ({{ attachment.file_size_kb | fileSizeKB }})
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
  props: ['attachment', 'fileType'],
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
      if(!this.attachment || !this.attachment.file) {
        return ''
      }
      const parts = this.attachment.file.split('/').filter(part => !!part)
      return parts[parts.length - 1]
    },
  },
}
</script>
