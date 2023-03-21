<template>
  <component
    v-if="value"
    v-model="value"
    :is="editView"
    :availableLearningmaterials="availableLearningmaterials"
    :course="course"
    ref="editor" />
</template>

<script>
  import courses from "../../../../logic/courses"
  import LearningmaterialEditor from "../../components/editors/LearningmaterialEditor"
  import FormEditor from "../../components/editors/FormEditor"
  import TodolistEditor from "../../components/editors/TodolistEditor"
  import AppointmentEditor from "../../components/editors/AppointmentEditor"
  import CertificateEditor from "../../components/editors/CertificateEditor"
  import QuestionsEditor from "../../components/editors/QuestionsEditor"

  export default {
    props: [
      'attachments',
      'course',
      'value',
    ],
    data() {
      return {
        availableLearningmaterials: null,
      }
    },
    created() {
      // reload available learning materials, maybe user created a new one in separate tab
      document.addEventListener('visibilitychange', this.reloadAvailableLearningmaterials, false)
    },
    destroyed() {
      document.removeEventListener('visibilitychange', this.reloadAvailableLearningmaterials)
    },
    watch: {
      value: {
        immediate: true,
        handler() {
          this.$emit('input', this.value)
          this.reloadAvailableLearningmaterials()
        },
      },
    },
    computed: {
      editView() {
        if(!this.value) {
          return null
        }
        if(this.value.type === courses.TYPE_LEARNINGMATERIAL) {
          return LearningmaterialEditor
        }
        if(this.value.type === courses.TYPE_FORM) {
          return FormEditor
        }
        if(this.value.type === courses.TYPE_APPOINTMENT) {
          return AppointmentEditor
        }
        if(this.value.type === courses.TYPE_CERTIFICATE) {
          return CertificateEditor
        }
        if(this.value.type === courses.TYPE_QUESTIONS) {
          return QuestionsEditor
        }
        if(this.value.type === courses.TYPE_TODOLIST) {
          return TodolistEditor
        }
      }
    },
    methods: {
      reloadAvailableLearningmaterials() {
        if (!this.value) {
          return
        }
        if (document.visibilityState === 'visible') {
          return axios.get(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`)
            .then((response) => {
              if(response.data.availableLearningmaterials) {
                this.availableLearningmaterials = response.data.availableLearningmaterials
              }
          })
        }
      },
      save() {
        return this.$refs.editor.save()
      },
    },
  }
</script>
