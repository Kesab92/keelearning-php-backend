<template>
  <v-alert
    outline
    type="info"
    :value="!material.visible && material.usages.length">
    Diese Datei wird an {{ material.usages.length }} {{ material.usages.length > 1 ? 'Stellen' : 'Stelle' }} verwendet.
    <router-link :to="{ name: `learningmaterials.edit.usages`,
        params: {
          folderId: this.material.learning_material_folder_id,
          learningmaterialId: this.material.id,
        }}">
      Verknüpfungen anzeigen
    </router-link>
    .
  </v-alert>
</template>

<script>
export default {
  props: ['material'],
}
</script>
