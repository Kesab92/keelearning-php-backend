<template>
  <div>
    <azure-video-progress :video-id="attachment.file" />
    <template v-if="attachment.file_size_kb">
      <br>
      {{ attachment.file_size_kb | fileSizeKB }}
    </template>
  </div>
</template>

<script>
export default {
  props: ['attachment'],
}
</script>
