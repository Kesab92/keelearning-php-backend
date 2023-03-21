<template>
  <div
    class="s-type"
    :class="{
      '-active': active === type,
    }"
    @mouseenter="$emit('select', type)">
    <v-icon small>{{ icon }}</v-icon>
    {{ label }}
  </div>
</template>

<script>
  import formMixin from '../../mixin'

  export default {
    mixins: [formMixin],
    props: ['type', 'active'],
    computed: {
      icon() {
        return this.typeIcons[this.type]
      },
      label() {
        return this.typeLabels[this.type]
      },
    }
  }
</script>

<style lang="scss" scoped>
  #app .s-type {
    cursor: pointer;
    width: 100%;
    padding: 12px 24px;
    &:hover, &.-active {
      background: #f1f1f1;
    }
  }
</style>
