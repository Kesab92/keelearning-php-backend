<template>
  <v-card
    v-click-outside="close"
    class="s-modal"
    :class="{
      '-up': up,
    }">
    <v-toolbar
      card
      dense>
      <v-toolbar-title class="body-2 black--text">Wählen Sie einen Inhaltstyp</v-toolbar-title>
    </v-toolbar>
    <v-layout row>
      <v-flex xs6>
        <div class="s-types">
          <ContentTypeListEntry
            v-for="type in types"
            :key="type.type"
            :type="type.type"
            :active="activeType"
            @click.native.stop="create(type.type)"
            @select="selectType" />
        </div>
      </v-flex>
      <v-flex xs6>
        <div class="s-details">
          <component :is="typeExplanationComponent" />
        </div>
      </v-flex>
    </v-layout>
  </v-card>
</template>

<script>
import {mapGetters} from "vuex"
  import ContentTypeListEntry from "./add-course-content-modal/ContentTypeListEntry"
  import TypeExplanationChapter from "./add-course-content-modal/TypeExplanationChapter"
  import TypeExplanationLearningmaterial from "./add-course-content-modal/TypeExplanationLearningmaterial"
  import TypeExplanationQuestions from "./add-course-content-modal/TypeExplanationQuestions"
  import TypeExplanationCertificate from "./add-course-content-modal/TypeExplanationCertificate"
  import TypeExplanationForm from "./add-course-content-modal/TypeExplanationForm"
  import TypeExplanationAppointment from "./add-course-content-modal/TypeExplanationAppointment"
  import TypeExplanationTodolist from "./add-course-content-modal/TypeExplanationTodolist"
  import ClickOutside from 'vue-click-outside'
  import courses from "../../../../logic/courses"

  export default {
    props: {
      up: {
        default: false,
        type: Boolean,
      },
    },
    data() {
      return {
        activeType: '',
      }
    },
    computed: {
      ...mapGetters({
        appSettings: 'app/appSettings',
      }),
      typeExplanationComponent() {
        const map = {
          [courses.TYPE_CHAPTER]: TypeExplanationChapter,
          [courses.TYPE_LEARNINGMATERIAL]: TypeExplanationLearningmaterial,
          [courses.TYPE_FORM]: TypeExplanationForm,
          [courses.TYPE_QUESTIONS]: TypeExplanationQuestions,
          [courses.TYPE_APPOINTMENT]: TypeExplanationAppointment,
          [courses.TYPE_CERTIFICATE]: TypeExplanationCertificate,
          [courses.TYPE_TODOLIST]: TypeExplanationTodolist,
        }
        return map[this.activeType]
      },
      types() {
        const types = [{
            type: courses.TYPE_CHAPTER,
          }]

        if(this.appSettings.module_learningmaterials == '1') {
          types.push({
            type: courses.TYPE_LEARNINGMATERIAL,
          })
        }
        if(this.appSettings.module_forms == '1') {
          types.push({
            type: courses.TYPE_FORM,
          })
        }
        if(this.appSettings.module_appointments == '1') {
          types.push({
            type: courses.TYPE_APPOINTMENT,
          })
        }

        types.push(
          {
            type: courses.TYPE_QUESTIONS,
          },
          {
            type: courses.TYPE_TODOLIST,
          },
          {
            type: courses.TYPE_CERTIFICATE,
          }
        )

        return types
      }
    },
    methods: {
      selectType(type) {
        this.activeType = type
      },
      create(type) {
        this.$emit('create', type)
      },
      close() {
        // We do this next tick, to give the modal enough time to emit any events
        this.$nextTick(() => {
          this.$emit('close')
        })
      },
    },
    directives: {
      ClickOutside,
    },
    components: {
      ContentTypeListEntry,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-modal {
    cursor: default;
    height: auto;
    left: 10px;
    pointer-events: all;
    position: absolute;
    top: 35px;
    width: 400px;
    z-index: 5;

    &.-up {
      bottom: 35px;
      top: auto;
    }
  }

  .s-types {
    height: 100%;
    overflow-y: auto;
  }

  #app .s-details {
    background: #f1f1f1;
    padding: 12px 24px;
    border-left: 1px solid #dedede;
    width: 100%;
    height: 100%;
  }
</style>
