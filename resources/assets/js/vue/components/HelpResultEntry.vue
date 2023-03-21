<template>
  <div>
    <span class="title">{{ page.title }}</span>
    <a
      v-if="page.type === 'faq'"
      href="help/faq">
      <v-btn color="primary">Zur Seite</v-btn>
    </a>
    <a
      :href="'help/knowledge?page=' + page.id"
      v-else>
      <v-btn color="primary">Zur Seite</v-btn>
    </a>
  </div>
</template>

<script>

  export default {
    props: {
      page: {
        type: Object,
        required: true
      }
    }
  }
</script>

<style lang="scss" scoped>
  #app {
    .title {
      width: 70%;
      display: inline-block;
    }
  }
</style>