<template>
  <div>
    <v-btn
      outline
      :href="materialData.file_url"
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
  props: ['materialData'],
  computed: {
    fileExtension() {
      const parts = this.materialData.file.split('.')
      return parts[parts.length - 1].toUpperCase()
    },
  },
}
</script>
