<template>
  <div>
    <v-text-field
      v-model="materialData.link"
      :rules="[$rules.url]"
      placeholder="https://ihre.domain.com"
      outline
      label="Link"
    >
      <template v-slot:append-outer>
        <v-btn
          class="s-openLink"
          color="secondary"
          outline
          :href="materialData.link"
          target="_blank"
          :disabled="!materialData.link"
        >
          Öffnen
        </v-btn>
      </template>
    </v-text-field>
  </div>
</template>

<script>
export default {
  props: ['materialData'],
}
</script>

<style lang="scss" scoped>
#app {
  .s-openLink {
    margin-top: -4px;
  }
}
</style>
