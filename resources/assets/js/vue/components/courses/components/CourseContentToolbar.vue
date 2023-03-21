<template>
  <div>
    <v-layout row>
      <v-flex xs5>
        <v-toolbar class="s-courseContentToolbar">
          <v-icon
            @click="goBack"
            medium
            title="Zurück">arrow_back</v-icon>
            <div class="subheading ml-2">
              {{ course.title }}
            </div>
            <v-spacer />
            <CourseContentInfo
              :course="course"
              subtle />
        </v-toolbar>
      </v-flex>
      <v-flex xs7>
        <v-toolbar>
          <v-btn
            :loading="isSaving"
            :disabled="isReadonly || !value"
            color="primary"
            @click="$emit('save')">
            Speichern
          </v-btn>
          <v-progress-circular
            v-if="!value"
            indeterminate
            color="primary"
            size="26"
            width="3"
            class="ml-4" />
          <toggle
            v-if="value && isContent"
            :value="value.visible"
            :disabled="isReadonly"
            :label="value.visible ? 'Inhalt sichtbar' : 'Inhalt nicht sichtbar'"
            class="mb-0 ml-4"
            :class="{'orange--text': !isReadonly && !value.visible}"
            @input="update('visible', $event)"/>
          <v-spacer/>
          <v-btn
            :loading="isSaving"
            :disabled="isReadonly || !value"
            color="red"
            outline
            @click="remove">
            Löschen
          </v-btn>
        </v-toolbar>
      </v-flex>
    </v-layout>
    <DeleteDialog
      v-if="value"
      v-model="deleteDialogOpen"
      :deletion-url="deleteDialogData.deletionUrl"
      :dependency-url="deleteDialogData.dependencyUrl"
      :entry-name="value.title"
      :redirect-url="`/courses#/courses/${course.id}/contents`"
      :type-label="deleteDialogData.typeLabel"
      @deleted="handleContentDeleted">
      <template
        v-if="isChapter"
        v-slot:append-message>
        <v-alert
          outline
          type="warning"
          class="mb-4"
          :value="true">
          Beim Löschen dieses Kapitels werden auch alle Statistiken und Zertifikate der zugehörigen Kursinhalte
          gelöscht.
        </v-alert>
      </template>
      <template
        v-if="isCertificate"
        v-slot:append-message>
        <p class="mt-3">Tipp:</p>
        <v-alert
          outline
          type="info"
          class="mb-4"
          :value="true">
          Statt das Zertifikat zu löschen, können Sie auch die Sichtbarkeit des Zertifikates anpassen.
          Das Hintergrundbild kann auch im Zertifikat ausgetauscht werden.
        </v-alert>
      </template>
      <template
        v-if="isCertificate"
        v-slot:info>
        <p>Möchten Sie das Zertifikat löschen?</p>
        <ul>
          <li class="orange--text">Alle Benutzer verlieren Zugriff auf dieses Zertifikat.</li>
        </ul>
      </template>
    </DeleteDialog>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import CourseContentInfo from '../../partials/courses/CourseContentInfo'
import DeleteDialog from '../../partials/global/DeleteDialog'

export default {
  props: ['course', 'isSaving', 'value'],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    baseRoute() {
      return `courses.${this.course.is_template ? 'templates.' : ''}edit`
    },
    deleteDialogData() {
      if (!this.value) {
        return {}
      }
      if (this.isChapter) {
        return {
          deletionUrl: `/backend/api/v1/courses/${this.$route.params.courseId}/chapter/${this.value.id}`,
          dependencyUrl: `/backend/api/v1/courses/${this.$route.params.courseId}/chapter/${this.value.id}/delete-information`,
          typeLabel: 'Kapitel',
        }
      }
      return {
        deletionUrl: `/backend/api/v1/courses/${this.$route.params.courseId}/content/${this.value.id}`,
        dependencyUrl: `/backend/api/v1/courses/${this.$route.params.courseId}/content/${this.value.id}/delete-information`,
        typeLabel: 'Kursinhalt',
      }
    },
    isChapter() {
      return this.$route.name === `${this.baseRoute}.contents.chapter`
    },
    isContent() {
      return this.$route.name === `${this.baseRoute}.contents.content`
    },
    isCertificate() {
      if(!this.value) {
        return false
      }
      return this.value.type === this.$constants.COURSES.TYPE_CERTIFICATE
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
  methods: {
    goBack() {
      this.$router.push({
          name: `${this.baseRoute}.general`,
          params: {
            courseId: this.$route.params.courseId,
          },
        })
    },
    handleContentDeleted() {
      this.$emit('delete')
      this.deleteDialogOpen = false
    },
    remove() {
      this.deleteDialogOpen = true
    },
    update(key, value) {
      this.$emit('input', {...this.value, [key]: value})
    },
  },
  components: {
    CourseContentInfo,
    DeleteDialog,
  },
}
</script>

<style lang="scss" scoped>
#app .s-courseContentToolbar ::v-deep .v-toolbar__content {
  padding: 0 16px; // bring padding in line with course contents
}
</style>
