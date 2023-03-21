<template>
  <div>
    <azure-video-progress ref="azureVideoProgress" :video-id="materialData.file" :cover-image="materialData.cover_image_url" />
    <br>
    {{ materialData.file_size_kb | fileSizeKB }}
  </div>
</template>

<script>
export default {
  props: ['materialData'],
  methods: {
    refresh() {
      this.$refs.azureVideoProgress.refresh()
    },
  },
}
</script>
