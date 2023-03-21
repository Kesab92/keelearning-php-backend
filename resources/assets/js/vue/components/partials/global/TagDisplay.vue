<template>
  <div>
    <v-chip
      v-for="tag in tags"
      :key="tag"
      disabled
      small>
      {{ tagLabel(tag) }}
    </v-chip>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  props: {
    tags: {
      required: true,
      type: Array,
    },
  },
  created() {
    this.$store.dispatch('tags/updateTags')
  },
  computed: {
    ...mapGetters({
      availableTags: 'tags/tags',
    }),
  },
  methods: {
    tagLabel(tagId) {
      const tag = this.availableTags.find(tag => tag.id === tagId)
      if(!tag) {
        return ''
      }
      return tag.label
    },
  },
}
</script>
