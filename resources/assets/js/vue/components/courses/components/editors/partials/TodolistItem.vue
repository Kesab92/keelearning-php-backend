<template>
  <div>
    <v-layout
      class="s-wrapper py-2"
      row
    >
      <div class="s-iconWrapper">
        <v-icon class="px-2" medium>checkmark</v-icon>
      </div>
      <div class="s-inputWrapper">
        <translated-input
          v-model="todolistItemData.title"
          :translations="todolistItemData.translations"
          attribute="title"
          input-type="input"
          label="Aufgabe"
          :style="`z-index: ${zIndex}`"
          class="mb-2"
        />
        <translated-input
          v-model="todolistItemData.description"
          :translations="todolistItemData.translations"
          attribute="description"
          input-type="texteditor"
          label="Ergänzende Informationen"
          class="hide-on-drag"
          :height="200"
          :style="`z-index: ${zIndex}`"
        />
      </div>
      <div class="s-deleteWrapper">
        <v-btn
          class="hide-on-drag"
          color="red"
          flat
          icon
          @click="deleteDialogOpen = true">
          <v-icon dark>delete</v-icon>
        </v-btn>
      </div>
    </v-layout>

    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/todolists/${todolistId}/items/${todolistItemData.id}`"
      :dependency-url="`/backend/api/v1/todolists/${todolistId}/items/${todolistItemData.id}/delete-information`"
      :entry-name="todolistItemData.title"
      type-label="Aufgabe"
      @deleted="handleTodolistItemDeleted">
    </DeleteDialog>
  </div>
</template>

<script>
import DeleteDialog from "../../../../partials/global/DeleteDialog.vue";

export default {
  components: {DeleteDialog},
  props: ["todolistItem", 'zIndex', 'todolistId'],
  data() {
    return {
      deleteDialogOpen: false,
      todolistItemData: null,
    }
  },
  watch: {
    todolistItem: {
      handler() {
        this.todolistItemData = JSON.parse(JSON.stringify(this.todolistItem))
      },
      immediate: true,
    },
    'todolistItemData.title': {
      handler() {
        this.$emit('updateTodolistItem', this.todolistItemData)
      },
    },
    'todolistItemData.description': {
      handler() {
        this.$emit('updateTodolistItem', this.todolistItemData)
      },
    },
  },
  methods: {
    handleTodolistItemDeleted() {
      this.deleteDialogOpen = false
      this.$emit('todolistItemDeleted', this.todolistItemData)
    },
  }
}
</script>

<style lang="scss" scoped>
#app {
  .s-wrapper {
    cursor: pointer;
    gap: 16px;

    &:hover {
      cursor: pointer;
      background: #f1f1f1;
    }
  }
  .s-iconWrapper {
    cursor: move;
    width: 40px;
    padding-top: 15px;
  }
  .s-inputWrapper{
    flex-grow: 1;
  }
  .s-deleteWrapper {
    padding-top: 4px;
  }
}
</style>
