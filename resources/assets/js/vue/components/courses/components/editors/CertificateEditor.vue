<template>
  <div>
    <translated-input
      v-model="value.title"
      :translations="value.translations"
      attribute="title"
      label="Name des Zertifikats"
      hide-details
      class="mb-3"
      :readOnly="isReadonly"/>
    <tag-select
      v-model="value.tags"
      label="Sichtbar für folgende User"
      placeholder="Alle"
      limit-to-tag-rights
      :disabled="isReadonly"
      class="mt-section"
      multiple />

    <v-text-field
      v-model="value.duration"
      placeholder="1"
      :disabled="isReadonly"
      label="Geschätzte Lerndauer"
      :suffix="'Minute' + ((value.duration && value.duration != 1) ? 'n' : '')" />

    <CertificateDesigner
      :certificate-id="value.foreign_id"
      :foreign-id="value.id"
      :isReadonly="isReadonly"
      type="course"
      :showPassedPercentage="false"
      @saved="handleCertificateSave">
      <template slot="header">
        <v-toolbar-title>Zertifikat bearbeiten</v-toolbar-title>
        <v-spacer/>
      </template>
    </CertificateDesigner>
  </div>
</template>

<script>
  import CertificateDesigner from "../../../CertificateDesigner"
  import TagSelect from "../../../partials/global/TagSelect"
  import {mapGetters} from "vuex";

  export default {
    props: [
      'course',
      'value',
    ],
    watch: {
      value() {
        this.$emit('input', this.value)
      },
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    methods: {
      handleCertificateSave(certificate) {
        this.value.foreign_id = certificate.id
      },
      save() {
        if(this.value.visible && !this.value.foreign_id) {
          alert('Bitte erstellen Sie ein Zertifikat, bevor Sie den Kursinhalt veröffentlichen.')
          return false
        }
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
      CertificateDesigner,
      TagSelect,
    },
  }
</script>
