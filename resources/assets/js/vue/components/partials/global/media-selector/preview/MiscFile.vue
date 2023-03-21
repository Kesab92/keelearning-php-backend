<template>
  <div>
    <v-btn
      outline
      :href="attachment.url"
      color="secondary"
      class="mx-0"
    >
      <v-icon left>cloud_download</v-icon>
      {{ fileExtension }} herunter laden
    </v-btn>
  </div>
</template>

<script>
export default {
  props: ['attachment'],
  computed: {
    fileExtension() {
      const parts = this.attachment.file.split('.')
      return parts[parts.length - 1].toUpperCase()
    },
  },
}
</script>
