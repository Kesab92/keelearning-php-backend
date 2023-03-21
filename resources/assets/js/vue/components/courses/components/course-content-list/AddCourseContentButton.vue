<template>
  <div
    class="s-buttonWrapper hide-on-drag"
    :class="{
      '-open': addCourseContentModalOpen,
    }">
    <div class="s-insertLine" />
    <v-btn
      fab
      dark
      color="primary"
      @click.stop="openModal"
      class="s-addButton">
      <v-icon
        small
        dark>add</v-icon>
    </v-btn>
    <AddCourseContentModal
      v-if="addCourseContentModalOpen"
      :up="openModalUpwards"
      @close="addCourseContentModalOpen = false"
      @create="createContent" />
  </div>
</template>

<script>
  import AddCourseContentModal from "./AddCourseContentModal"
  export default {
    props: ['course', 'chapterId', 'position'],
    data() {
      return {
        addCourseContentModalOpen: false,
        isAdding: false,
        openModalUpwards: false,
      }
    },
    methods: {
      openModal() {
        // this button sticks to the bottom of the wrapping element
        let offsetElement = this.$el.offsetParent
        if (!offsetElement) {
          offsetElement = this.$el
        }
        const yPosition = offsetElement.offsetTop + offsetElement.offsetHeight
        const sidebarHeight = offsetElement.closest('.js-sidebar').offsetHeight
        // open the modal up instead of down from the button if we can't fit it in the remaining height
        this.openModalUpwards = sidebarHeight - yPosition < 250
        this.addCourseContentModalOpen = true
      },
      createContent(type) {
        if(this.isAdding) {
          return
        }
        this.isAdding = true
        axios.post(`/backend/api/v1/courses/${this.course.id}/content`, {
          type,
          position: this.position,
          chapter: this.chapterId,
        })
          .then((response) => {
            this.$emit('contentAdded', {
              response: response.data,
              type,
            })
            this.$nextTick(() => {
              // It's important to call this after emitting the event, because otherwise this component is destroyed before it can emit the event
              this.addCourseContentModalOpen = false
            })
          })
          .catch(() => {
            alert('Der Inhalt konnte leider nicht hinzugefügt werden. Bitte probieren Sie es später erneut.')
          })
        .finally(() => {
          this.isAdding = false
        })
      },
    },
    components: {
      AddCourseContentModal,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-buttonWrapper {
    position: absolute;
    display: none;
    bottom: -15px;
    left: 10px;
    right: 0;
    pointer-events: none;

    &:hover, &.-open {
      display: block;
    }
  }

  #app .s-addButton {
    height: 24px;
    pointer-events: all;
    width: 24px;
  }

  #app .s-insertLine {
    position: absolute;
    border-bottom: dashed 2px #b1b1b1;
    right: 0;
    left: -10px;
    top: 20px;
  }
</style>
