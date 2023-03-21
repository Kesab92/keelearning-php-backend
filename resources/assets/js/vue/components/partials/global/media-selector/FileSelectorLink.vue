<template>
  <div>
    <v-text-field
      :value="attachment.link"
      @input="update($event)"
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
          :href="attachment.link"
          target="_blank"
          :disabled="!attachment.link"
        >
          Öffnen
        </v-btn>
      </template>
    </v-text-field>
  </div>
</template>

<script>
export default {
  props: ['value'],
  computed: {
    attachment() {
      return this.value ? this.value : null
    },
  },
  methods: {
    update(link) {
      this.$emit('input', {...this.attachment, link:link})
    },
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-openLink {
    margin-top: -4px;
  }
}
</style>
