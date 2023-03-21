<template>
  <div
    class="s-courseContentContentsWrapper"
    ref="wrapper">
    <div class="px-4 py-1">
      <v-text-field
        append-icon="search"
        autofocus
        clearable
        placeholder="Inhalt / Kapitel"
        single-line
        v-model="search" />
    </div>
    <Draggable
      v-model="chapters"
      :disabled="isReadonly"
      :group="{
        name: 'chapters',
        put: false,
        pull: false,
      }"
      @change="handleChapterReorder"
      @end="stopDrag"
      @start="startDrag">
      <div
        v-for="chapter in chapters"
        :key="chapter.id">
      <CourseChapter
          :ref="`chapter-${chapter.id}`"
          :chapter="chapter"
          :course="course"
          :is-active="isActiveChapter(chapter.id)"
          @contentAdded="handleContentAdded" />
        <Draggable
          v-model="chapter.contents"
          :disabled="isReadonly"
          :group="{ name: `chapter-${chapter.id}`, put: !isReadonly, pull: !isReadonly, }"
          @change="handleReorder($event, chapter)"
          class="hide-on-drag">
          <CourseContent
            v-for="content in chapter.contents"
            :key="content.id"
            :content="content"
            :chapter="chapter"
            :course="course"
            :is-active="isActiveContent(content.id)"
            @contentAdded="handleContentAdded"/>
        </Draggable>
        <div
          v-if="!chapter.contents.length"
          class="pa-3 hide-on-drag">
          <template v-if="!search">
            {{ chapter.title }} hat noch keine Inhalte.<br>
          </template>
          <v-btn
            v-if="!isReadonly"
            flat
            small
            class="ml-0"
            @click.stop="openAddContentModal(chapter.id)">
            <v-icon dark>add</v-icon>
            Inhalt einfügen
          </v-btn>
        </div>
      </div>
    </Draggable>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex'
  import Draggable from 'vuedraggable'
  import CourseContent from './CourseContent.vue'
  import CourseChapter from './CourseChapter.vue'
  import Helpers from '../../../../logic/helpers'


  export default {
    props: ['course'],
    data() {
      return {
        chapters: [],
        search: '',
      }
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      filteredSortedChapters() {
        let chapters = JSON.parse(JSON.stringify(this.course.chapters))
        let searchTerm = null
        if (this.search) {
          searchTerm = this.search.toLowerCase()
        }
        if(!chapters.length) {
          return chapters
        }
        chapters.sort((a, b) => {
          return a.position - b.position
        })
        chapters.forEach(chapter => {
          if (searchTerm) {
            chapter.contents = chapter.contents.filter(content => {
              if(content.type === this.$constants.COURSES.TYPE_LEARNINGMATERIAL && content.relatable) {
                if (content.relatable.title.toLowerCase().includes(searchTerm)) {
                  return true
                }
              }
              return content.title.toLowerCase().includes(searchTerm)
            })
          }
          chapter.contents.sort((a, b) => {
            return a.position - b.position
          })
        })
        if (searchTerm) {
          chapters = chapters.filter(chapter => {
            return chapter.contents.length || chapter.title.toLowerCase().includes(searchTerm)
          })
        }
        return chapters
      },
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    watch: {
      filteredSortedChapters: {
        handler() {
          this.chapters = this.filteredSortedChapters
        },
        immediate: true,
      },
    },
    methods: {
      handleContentAdded(data) {
        this.$emit('contentAdded', data)
      },
      isActiveChapter(id) {
        return parseInt(this.$route.params.chapterId, 10) === id
      },
      isActiveContent(id) {
        return parseInt(this.$route.params.contentId, 10) === id
      },
      openAddContentModal(chapterId) {
        this.$refs['chapter-' + chapterId][0].openModal()
      },
      handleReorder(data, chapter) {
        let newCourse = JSON.parse(JSON.stringify(this.course))
        chapter = newCourse.chapters.find(c => c.id === chapter.id)
        if(!chapter) {
          return
        }
        chapter.contents.sort((a, b) => {
          return a.position - b.position
        })
        if(data.moved) {
          this.handleListMove(data, chapter)
        }
        if(data.removed) {
          this.handleListRemove(data, chapter)
        }
        if(data.added) {
          this.handleListAdd(data, chapter)
        }
        chapter.contents.forEach((content, idx) => {
          content.position = idx
        })
        this.$emit('updateContentPositions', chapter)
      },
      handleChapterReorder(data) {
        let newCourse = JSON.parse(JSON.stringify(this.course))
        newCourse.chapters.sort((a, b) => {
          return a.position - b.position
        })
        newCourse.chapters = Helpers.reorder(newCourse.chapters, data.moved.oldIndex, data.moved.newIndex)
        newCourse.chapters.forEach((chapter, idx) => {
          chapter.position = idx
        })
        this.$emit('updateChapterPositions', newCourse.chapters)
      },
      handleListRemove(data, chapter) {
        chapter.contents.splice(data.removed.oldIndex, 1)
      },
      handleListAdd(data, chapter) {
        data.added.element.course_chapter_id = chapter.id
        chapter.contents.splice(data.added.newIndex, 0, data.added.element)
      },
      handleListMove(data, chapter) {
        chapter.contents = Helpers.reorder(chapter.contents, data.moved.oldIndex, data.moved.newIndex)
      },
      startDrag() {
        // We're running in a bug with vue-draggable
        // where *any* change to the DOM made by Vue on the
        // drag-start event will cause vue-draggable to
        // lose functionality.
        // This is probably caused by an interaction between
        // our stack and the vue-draggable plugin, since
        // this does not happen on a minimal test case
        // with only vue-draggable.
        this.$refs.wrapper.classList.add('js-dragging')
      },
      stopDrag() {
        this.$refs.wrapper.classList.remove('js-dragging')
      },
    },
    components: {
      CourseChapter,
      CourseContent,
      Draggable,
    }
  }
</script>

<style lang="scss">
#app .s-courseContentContentsWrapper .sortable-ghost .s-wrapper {
  background: #616161 !important;
  color: white;
  box-shadow: inset 0 5px 5px -5px rgba(0, 0, 0, 0.42);
}

.s-courseContentContentsWrapper {
  .sortable-ghost,
  .sortable-drag,
  .sortable-is-dragging,
  .js-dragging {
    .hide-on-drag {
      display: none !important;
    }
  }
}
</style>
