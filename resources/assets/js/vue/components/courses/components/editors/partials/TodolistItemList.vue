<template>
  <div
    class="s-todolistContentsWrapper"
    ref="wrapper">
    <Draggable
      v-model="todolistItemsData"
      @change="handleReorder"
      @end="stopDrag"
      @start="startDrag"
      filter=".v-input"
      :preventOnFilter="false">
      <TodolistItem
        v-for="(item, index) in todolistItemsData"
        :key="item.id"
        :todolist-item="item"
        :todolist-id="todolistId"
        :z-index="todolistItemsData.length - index"
        @todolistItemDeleted="handleDeleteTodolistItem"
        @updateTodolistItem="handleUpdateTodolistItem"
      />
    </Draggable>
  </div>
</template>

<script>
import Draggable from 'vuedraggable'
import TodolistItem from "./TodolistItem.vue";
import Helpers from "../../../../../logic/helpers";

export default {
  props: ['todolistItems', 'todolistId'],
  data() {
    return {
      todolistItemsData: null,
    }
  },
  watch: {
    todolistItems: {
      handler() {
        this.todolistItemsData = JSON.parse(JSON.stringify(this.todolistItems))
      },
      immediate: true,
    },
  },
  methods: {
    handleDeleteTodolistItem(deletedField) {
      this.todolistItemsData = this.todolistItemsData.filter(field => {
        return field.id !== deletedField.id
      })

      this.$emit('update:todolistItems', this.todolistItemsData)
    },
    handleReorder(data) {
      this.todolistItemsData.sort((a, b) => {
        return a.position - b.position
      })
      this.todolistItemsData = Helpers.reorder(this.todolistItemsData, data.moved.oldIndex, data.moved.newIndex)
      this.todolistItemsData.forEach((item, idx) => {
        item.position = idx
      })
      this.$emit('update:todolistItems', this.todolistItemsData)
    },
    handleUpdateTodolistItem(updatedField) {
      this.todolistItemsData.forEach((field, index) => {
        if(field.id === updatedField.id) {
          this.todolistItemsData[index] = updatedField
        }
      })
      this.$emit('update:todolistItems', this.todolistItemsData)
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
    TodolistItem,
    Draggable,
  }
}
</script>

<style lang="scss">
#app .s-todolistContentsWrapper .sortable-ghost .s-wrapper {
  background: #616161 !important;
  color: white;
  box-shadow: inset 0 5px 5px -5px rgba(0, 0, 0, 0.42);
  * {
    color: white !important;
  }
}
.s-todolistContentsWrapper {
  .sortable-is-dragging,
  &.js-dragging {
    .hide-on-drag {
      display: none !important;
    }
  }
}
</style>
