<template>
  <router-link
    tag="div"
    :to="{
      name: `${baseRoute}.contents.chapter`,
      params: {
        courseId: course.id.toString(), // parsed url parameters are strings, we dont want
        chapterId: chapter.id.toString(), // to accidentally cause a refetch
      },
    }">
    <v-layout
      row
      :class="{
        '-active' : isActive,
      }"
      class="px-3 py-2 s-wrapper">
      <v-flex align-self-end class="body-2">
        {{ chapter.title }}
      </v-flex>
      <v-flex class="text-xs-right">
        <CourseContentInfo
          :chapter="chapter"
          subtle />
      </v-flex>
      <AddCourseContentButton
        v-if="!isReadonly"
        :chapter-id="chapter.id"
        :position="0"
        :course="course"
        ref="addContentButton"
        @contentAdded="handleContentAdded" />
    </v-layout>
  </router-link>
</template>

<script>
  import {mapGetters} from 'vuex'
  import AddCourseContentButton from './AddCourseContentButton'
  import CourseContentInfo from '../../../partials/courses/CourseContentInfo'

  export default {
    props: [
      'chapter',
      'course',
      'isActive',
    ],
    methods: {
      handleContentAdded(data) {
        this.$emit('contentAdded', data)
      },
      openModal() {
        this.$refs.addContentButton.openModal()
      },
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      baseRoute() {
        return `courses.${this.course.is_template ? 'templates.' : ''}edit`
      },
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    components: {
      AddCourseContentButton,
      CourseContentInfo,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-wrapper {
    background: #e8e8e8;
    position: relative;
    cursor: pointer;

    &.-active {
      background: #eaf1ff;

      &:after {
        content: "";
        display: block;
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #1976d2;
      }
    }

    &:hover {
      z-index: 2;
      ::v-deep .s-buttonWrapper {
        display: block;
      }
    }
  }
</style>
