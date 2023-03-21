<template>
  <div class="mb-4">
    <translated-input
      v-model="value.title"
      :translations="value.translations"
      :placeholder="currentForm.title"
      attribute="title"
      label="Name vom Kursinhalt"
      hide-details
      :readOnly="isReadonly"/>
    <v-autocomplete
      v-model="value.foreign_id"
      :items="availableForms"
      label="Formular"
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
        v-if="!isReadonly && myRights['forms-edit']"
        target="_blank"
        href="/forms?#/forms">Neues Formular erstellen</a>
    </div>

    <tag-select
      v-model="value.tags"
      label="Sichtbar für folgende User"
      placeholder="Alle"
      class="mt-section"
      limit-to-tag-rights
      :disabled="isReadonly"
      multiple/>
    <v-text-field
      v-model="value.duration"
      placeholder="1"
      :disabled="isReadonly"
      label="Geschätzte Lerndauer"
      :suffix="'Minute' + ((value.duration && value.duration != 1) ? 'n' : '')"/>
    <div
      v-if="value.foreign_id"
      class="my-2">
      <h4>Inhalte des Formulars</h4>
      <router-link
        v-if="myRights['forms-edit']"
        :to="{name: 'forms.edit.general', params: {formId: value.foreign_id}}">
        Formular öffnen
      </router-link>
    </div>
    <FormFieldList
      class="grey lighten-4"
      :form-fields="currentForm.fields" />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
  import TextEditor from "../../../partials/global/TextEditor"
  import TagSelect from "../../../partials/global/TagSelect"
  import FormFieldList from "./partials/FormFieldList"

  export default {
    props: [
      'course',
      'value',
    ],
    created() {
      this.loadAllForms()
    },
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
        allForms: 'forms/allForms',
        myRights: 'app/myRights',
      }),
      availableForms() {
        if(!this.allForms.length) {
          return []
        }
        return this.allForms.filter(form => {
          if(form.id === this.value.foreign_id) {
          return true
          }

          return !form.is_archived && !form.is_draft
        })
      },
      currentForm() {
        if(!this.value.foreign_id || !this.availableForms.length) {
          return {}
        }
        return this.availableForms.find(form => form.id === this.value.foreign_id)
      },
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    methods: {
      loadAllForms() {
        this.$store.dispatch('forms/loadAllForms')
      },
      save() {
        return axios.post(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`, {
          duration: this.value.duration,
          foreign_id: this.value.foreign_id,
          tags: this.value.tags,
          title: this.value.title,
          visible: this.value.visible,
        })
      },
    },
    components: {
      FormFieldList,
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
