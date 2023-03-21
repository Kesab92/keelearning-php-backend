<template>
  <v-tooltip bottom>
    <template slot="activator">
      <div class="s-icon">
        <v-icon
          v-text="info.icon"
          :small="subtle"
          class="mr-1" />
        <template v-if="count !== null">
          {{ count }}
        </template>
      </div>
    </template>
    <span>
      {{ info.label }}
    </span>
  </v-tooltip>
</template>

<script>
export default {
  props: {
    count: {
      default: null,
    },
    subtle: {
      default: false,
      required: false,
      type: Boolean,
    },
    type: {
      required: true,
      type: Number,
    },
  },
  computed: {
    info() {
      return {
        [this.$constants.COURSES.TYPE_APPOINTMENT]: {
          icon: 'event',
          label: 'Termin',
        },
        [this.$constants.COURSES.TYPE_CERTIFICATE]: {
          icon: 'verified',
          label: 'Zertifikat',
        },
        [this.$constants.COURSES.TYPE_CHAPTER]: {
          icon: 'bookmark_border',
          label: 'Kapitel',
        },
        [this.$constants.COURSES.TYPE_FORM]: {
          icon: 'view_list',
          label: 'Formular',
        },
        [this.$constants.COURSES.TYPE_LEARNINGMATERIAL]: {
          icon: 'ondemand_video',
          label: 'Datei',
        },
        [this.$constants.COURSES.TYPE_QUESTIONS]: {
          icon: 'list_alt',
          label: 'Lernfragen',
        },
        [this.$constants.COURSES.TYPE_TODOLIST]: {
          icon: 'checklist',
          label: 'Aufgabenliste',
        },
      }[this.type]
    },
  },
}
</script>

<style lang="scss" scoped>
.s-icon {
  align-items: center;
  display: inline-flex;
  vertical-align: bottom;
}
</style>
