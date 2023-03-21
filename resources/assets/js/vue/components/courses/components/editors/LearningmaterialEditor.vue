<template>
  <div>
    <v-layout
      row
      class="mb-4">
      <v-flex
        v-if="currentLearningmaterial.cover_image_url"
        class="pr-4"
        shrink>
        <img
          class="s-contentImage"
          :src="currentLearningmaterial.cover_image_url">
      </v-flex>
      <v-flex>
        <translated-input
          v-model="value.title"
          :translations="value.translations"
          :placeholder="currentLearningmaterial.title"
          attribute="title"
          label="Name vom Kursinhalt"
          hide-details
          :readOnly="isReadonly"/>
        <v-autocomplete
          v-model="value.foreign_id"
          :items="availableLearningmaterials"
          label="Datei"
          item-text="title"
          item-value="id"
          hide-details
          :disabled="isReadonly"
          clearable
          class="mb-1"
        >
          <template v-slot:selection="data">
            {{ data.item ? data.item.title : '' }}
          </template>
        </v-autocomplete>
        <div class="mb-3">
          <a
            v-if="!isReadonly"
            target="_blank"
            :href="createFileLink">Erstellen Sie an dieser Stelle eine neue Datei</a>
        </div>

        <tag-select
          v-model="value.tags"
          label="Sichtbar für folgende User"
          placeholder="Alle"
          class=" mt-section"
          limit-to-tag-rights
          :disabled="isReadonly"
          multiple/>
        <v-text-field
          v-model="value.duration"
          placeholder="1"
          :disabled="isReadonly"
          label="Geschätzte Lerndauer"
          :suffix="'Minute' + ((value.duration && value.duration != 1) ? 'n' : '')"  />
      </v-flex>
    </v-layout>

    <translated-input
      v-model="value.description"
      input-type="texteditor"
      :translations="value.translations"
      attribute="description"
      :height="600"
      label="Hier können Sie die Beschreibung der Datei überschreiben"
      :readOnly="isReadonly"/>
  </div>
</template>

<script>
  import TextEditor from "../../../partials/global/TextEditor"
  import TagSelect from "../../../partials/global/TagSelect"
  import {mapGetters} from "vuex";

  export default {
    props: [
      'availableLearningmaterials',
      'course',
      'value',
    ],
    watch: {
      value: {
        handler() {
          this.$emit('input', this.value)
        },
        deep: true,
      },
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      createFileLink() {
        return window.VUEX_STATE.relaunchBackendUIUrl + '/learningmaterials?#/learningmaterials'
      },
      currentLearningmaterial() {
        if(!this.value.foreign_id || !this.availableLearningmaterials) {
          return {}
        }
        return this.availableLearningmaterials.find(learningmaterial => learningmaterial.id === this.value.foreign_id)
      },
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    methods: {
      save() {
        return axios.post(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`, {
          description: this.value.description,
          duration: this.value.duration,
          foreign_id: this.value.foreign_id,
          tags: this.value.tags,
          title: this.value.title,
          visible: this.value.visible,
        })
      },
    },
    components: {
      TextEditor,
      TagSelect,
    },
  }
</script>


<style lang="scss" scoped>
#app .s-contentImage {
  max-width: 200px;
  max-height: 230px;
  object-fit: cover;
}
</style>
