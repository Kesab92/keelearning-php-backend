<template>
  <div>
    <div class="subheading mb-2">
      <template v-if="createTemplate">
        Erstellen Sie eine neue Kurs-Vorlage
        <template v-if="selectedTemplate">
          auf Basis einer bestehenden Vorlage
        </template>
      </template>
      <template v-else>
        Erstellen Sie einen neuen Kurs
        <template v-if="selectedTemplate">
          aus dieser Vorlage
        </template>
      </template>
    </div>
    <v-layout row>
      <v-flex v-if="selectedTemplate" xs6 pa-2>
        <div class="s-template__image -small mb-2">
          <img :src="selectedTemplate.cover_image_url || '/img/no-connection.svg'">
        </div>
        <CourseContentInfo :course="selectedTemplate" />
        <div
          v-text="description"
          class="body-1 mt-4" />
      </v-flex>
      <v-flex
        :xs6="selectedTemplate"
        :xs12="!selectedTemplate"
        pa-2>
        <v-text-field
          :value="value.title"
          @input="update('title', $event)"
          label="Name"
          hide-details
          required
          class="mb-3 mt-2"
          box />
        <tag-select
          v-if="!this.isFullAdmin"
          :value="value.tags"
          @input="update('tags', $event)"
          color="blue-grey lighten-2"
          label="Sichtbar für folgende User"
          multiple
          outline
          placeholder="Alle"
          limitToTagRights
        />
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import CourseContentInfo from '../../../partials/courses/CourseContentInfo'
import TagSelect from '../../../partials/global/TagSelect'

export default {
  props: [
    'createTemplate',
    'selectedTemplate',
    'value',
  ],
  computed: {
    ...mapGetters({
      isFullAdmin: 'app/isFullAdmin',
    }),
    description() {
      if (!this.selectedTemplate.description) {
        return null;
      }
      let description = this.$options.filters.stripHtml(this.selectedTemplate.description);
      if (description.length > 400) {
        description = description.substring(0, 400) + '…'
      }
      return description
    },
  },
  methods: {
    update(key, value) {
      this.$emit('input', {...this.value, [key]: value})
    },
  },
  components: {
    CourseContentInfo,
    TagSelect,
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-template__image {
    position: relative;

    &.-small {
      max-width: 250px;
    }

    &::after {
      content: '';
      display: block;
      padding-bottom: 50%;
      width: 100%;
    }

    img {
      bottom: 0;
      height: 100%;
      left: 0;
      object-fit: cover;
      position: absolute;
      right: 0;
      top: 0;
      width: 100%;

      &[src$=".svg"] {
        object-fit: fill;
      }
    }
  }
}
</style>
