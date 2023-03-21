<template>
  <div v-if="formData && myRights['forms-edit']">
    <FormToolbar
      :form-data="formData"
      :is-valid="isValid"
      @save="saveAction"
      @publish="publishAction"
    />
    <v-form v-model="isValid">
      <div class="pa-4">
        <v-layout
          row
          class="mb-2">
          <v-flex xs6>
            <translated-input
              v-model.trim="formData.title"
              :translations="formData.translations"
              :rules="[$rules.minChars(3)]"
              attribute="title"
              class="mb-2"
              style="z-index: 2;"
              label="Bezeichnung"/>
            <tag-select
              v-model="formData.tags"
              color="blue-grey lighten-2"
              class="mb-2"
              label="Sichtbar für folgende User"
              multiple
              outline
              placeholder="Alle"
              :limitToTagRights="true"
            />
            <content-category-select
              v-model="formData.categories"
              label="Kategorie"
              :type="$constants.CONTENT_CATEGORIES.TYPE_FORMS"
              multiple />
          </v-flex>

          <v-flex xs6 class="ml-4">
            <ImageUploader
              :current-image="formData.cover_image_url"
              name="Coverbild"
              width="100%"
              height="auto"
              :url="`/backend/api/v1/forms/${formData.id}/cover`"
              @newImage="handleNewImage"/>
          </v-flex>
        </v-layout>
        <h4 class="mt-section mb-0 sectionHeader">Welche Felder soll das Formular enthalten?</h4>
        <p>Fügen Sie dem Formular beliebig viele Felder hinzu und bestimmen Sie die Reihenfolge per Drag & Drop.</p>
        <FormFieldList
          :form-fields.sync="formData.fields"
          @formFieldDeleted="formFieldDeleted"
        />
        <AddFormFieldButton
          v-if="isPrimaryLanguage"
          class="mt-4 s-addFormFieldButton"
          :form="formData"
          :position="newFormFieldPosition"
          @formFieldAdded="formFieldAdded"
        />
      </div>
    </v-form>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import AddFormFieldButton from "../components/AddFormFieldButton"
import FormToolbar from "../components/FormToolbar"
import ImageUploader from "../../partials/global/ImageUploader"
import TagSelect from "../../partials/global/TagSelect"
import FormFieldList from "../components/form-field-list/FormFieldList";

export default {
  props: ["form"],
  data() {
    return {
      formData: null,
      isValid: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      isPrimaryLanguage: 'languages/isPrimaryLanguage',
    }),
    isSaving: {
      get() {
        return this.$store.state.forms.isSaving
      },
      set(data) {
        this.$store.commit('forms/setIsSaving', data)
      },
    },
    newFormFieldPosition() {
      return this.formData.fields.length + 1
    },
  },
  watch: {
    form: {
      handler() {
        this.formData = JSON.parse(JSON.stringify(this.form))
      },
      immediate: true,
    },
  },
  methods: {
    handleNewImage(image) {
      this.formData.cover_image_url = image
    },
    publishAction() {
      this.formData.is_draft = false
      this.save()
    },
    saveAction() {
      this.save()
    },
    save() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      return this.$store.dispatch("forms/saveForm", {
        categories: this.formData.categories,
        cover_image_url: this.formData.cover_image_url,
        fields: this.formData.fields,
        id: this.formData.id,
        is_draft: this.formData.is_draft,
        tags: this.formData.tags,
        title: this.formData.title,
      }).catch(() => {
        alert('Beim Speichern ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isSaving = false
      })
    },
    formFieldAdded(newFormField) {
      this.formData.fields.push(newFormField)
    },
    formFieldDeleted(formFields) {
      this.formData.fields = formFields
    },
  },
  components: {
    AddFormFieldButton,
    FormFieldList,
    FormToolbar,
    ImageUploader,
    TagSelect,
  },
}
</script>
<style>
#app .s-addFormFieldButton{
  /* It has to be adjust to the other contents. Children contain margin from Vuetify CSS */
  margin-left: -10px;
}
</style>
