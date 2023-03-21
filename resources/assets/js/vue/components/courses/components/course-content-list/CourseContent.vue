<template>
  <router-link
    tag="div"
    :to="{
      name: `${baseRoute}.contents.content`,
      params: {
        courseId: course.id.toString(), // parsed url parameters are strings, we dont want
        contentId: content.id.toString(), // to accidentally cause a refetch
      },
    }">
    <v-layout
      row
      align-start
      class="pa-3 s-wrapper"
      :class="{
        '-active' : isActive,
        '-invisible': !content.visible,
    }">
      <v-flex
        align-self-center
        class="mr-2"
        xs1>
        <v-icon
          v-if="!content.visible"
          small>visibility_off</v-icon>
      </v-flex>
      <v-flex class="s-name pl-2" xs8>
        <div class="subheading">
          {{ title || '[Neuer Inhalt]' }}
        </div>
        <div class="caption grey--text">
          {{ typeLabel }}
        </div>
        <div v-if="content.tags">
          <v-chip
            class="ml-0"
            :key="`${content.id}-${tag.id}`"
            disabled
            small
            v-for="tag in content.tags">
            {{ tag.label }}
          </v-chip>
        </div>
      </v-flex>
      <v-flex xs3 class="text-xs-right text-no-wrap">
        <v-layout
          align-center
          justify-end
          row>
          <CourseContentIcon :type="content.type" />
          <div
            class="ml-2"
            style="min-width:40px">
            <template v-if="content.duration">
              {{ content.duration }} min
            </template>
          </div>
        </v-layout>
      </v-flex>
      <AddCourseContentButton
        v-if="!isReadonly"
        :chapter-id="content.course_chapter_id"
        :position="newContentPosition"
        :course="course"
        @contentAdded="contentAdded" />
    </v-layout>
  </router-link>
</template>

<script>
  import {mapGetters} from "vuex"
  import AddCourseContentButton from "./AddCourseContentButton"
  import courses from "../../../../logic/courses"
  import CourseContentIcon from "../../../partials/courses/CourseContentIcon"

  export default {
    props: [
      'chapter',
      'content',
      'course',
      'isActive',
    ],
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      typeLabel() {
        return courses.contentTypeLabels[this.content.type]
      },
      baseRoute() {
        return `courses.${this.course.is_template ? 'templates.' : ''}edit`
      },
      title() {
        if(this.content.title) {
          return this.content.title
        }
        const typesWithRelatable = [
          courses.TYPE_APPOINTMENT,
          courses.TYPE_FORM,
          courses.TYPE_LEARNINGMATERIAL,
        ]
        if(typesWithRelatable.includes(this.content.type) && this.content.relatable) {
          return this.content.relatable.title
        }
        return ''
      },
      newContentPosition() {
        return this.content.position + 1
      },
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    methods: {
      contentAdded(data) {
        this.$emit('contentAdded', data)
      },
    },
    components: {
      AddCourseContentButton,
      CourseContentIcon,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-wrapper {
    position: relative;

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

    &.-invisible {
      .s-position, .s-name {
        opacity: 0.4;
      }
      .s-position {
        filter: grayscale(1);
      }
    }

    &:hover {
      cursor: pointer;
      background: #f1f1f1;
      z-index: 2;

      &.-active {
        background: #eaf1ff;
      }

      ::v-deep .s-buttonWrapper {
        display: block;
      }
    }
  }

  #app .flex.s-position {
    width: 50px;
    flex-shrink: 1;
    flex-grow: 0;
    color: white;
    border-radius: 3px;
    display: flex;
    justify-content: space-between;
    padding: 2px 5px;
    margin-top: -1px;
  }
</style>
