<template>
  <div>
    <div
      class="s-draftLabel d-inline-block py-1 px-2"
      :class="{
      'grey': !isDraft,
      'lighten-2': !isDraft,
      'yellow': isDraft,
    }"
    >
      {{ draftLabel }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isDraft: {
      type: Boolean,
      required: true,
    }
  },
  computed: {
    draftLabel() {
      if(this.isDraft) {
        return 'Entwurf'
      }

      return 'Veröffentlicht'
    }
  }
}
</script>
<style lang="scss" scoped>
  .s-draftLabel{
    border-radius: 3px;
  }
</style>
