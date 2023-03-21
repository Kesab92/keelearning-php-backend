<template>
  <div class="mb-4">
    <translated-input
      v-model="value.title"
      :translations="value.translations"
      attribute="title"
      label="Name der Aufgabenliste"
      hide-details
      :readOnly="isReadonly"/>

    <tag-select
      v-model="value.tags"
      label="Sichtbar für folgende User"
      placeholder="Alle"
      class="mt-section"
      limit-to-tag-rights
      :disabled="isReadonly"
      multiple/>

    <v-text-field
      v-model="value.duration"
      placeholder="1"
      :disabled="isReadonly"
      label="Geschätzte Lerndauer"
      :suffix="'Minute' + ((value.duration && value.duration != 1) ? 'n' : '')"/>

    <v-layout row class="mt-section">
      <v-flex>
        <h4 class="mb-0 sectionHeader">Aufgabenliste</h4>
        <p>Fügen Sie eine oder mehrere Aufgaben hinzu, die bearbeitet werden müssen bevor der Kurs fortgesetzt werden
          kann.
          Sie können die Aufgaben per Drag & Drop verschieben.</p>
      </v-flex>
      <v-flex shrink>
        <v-btn
          :to="`/courses/${course.id}/todolists/${value.id}`"
          flat>
          <v-icon left>trending_up</v-icon>
          Statistiken
        </v-btn>
      </v-flex>
    </v-layout>


    <TodolistItemList
      v-if="todolistItems !== null"
      :todolistItems.sync="todolistItems"
      :todolist-id="value.foreign_id"
    />
    <div v-else>
      <v-progress-circular indeterminate/>
    </div>
    <v-btn
      v-if="isPrimaryLanguage"
      fab
      dark
      color="primary"
      @click.stop="addTodolistItem"
      class="mt-4 s-addButton">
      <v-icon
        dark>add
      </v-icon>
    </v-btn>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import TagSelect from "../../../partials/global/TagSelect"
import TodolistItemList from "./partials/TodolistItemList.vue"

export default {
  props: [
    "course",
    "value",
  ],
  data() {
    return {
      todolistItems: null,
    }
  },
  created() {
    axios.get(`/backend/api/v1/todolists/${this.value.foreign_id}/items`).then((response) => {
      this.todolistItems = response.data.todolistItems
    }).catch(() => {
      alert("Die Aufgabenliste konnte leider nicht geladen werden. Bitte versuchen Sie es später erneut.")
    })
  },
  watch: {
    value: {
      handler() {
        this.$emit("input", this.value)
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      myRights: "app/myRights",
      isPrimaryLanguage: "languages/isPrimaryLanguage",
    }),
    isReadonly() {
      return !this.myRights["courses-edit"]
    },
  },
  methods: {
    save() {
      if (this.value.visible && !this.todolistItems.length) {
        alert("Bitte legen Sie mindestens eine Aufgabe an, bevor Sie den Kursinhalt veröffentlichen.")
        return false
      }
      if (this.todolistItems.some(item => !item.title)) {
        alert("Bitte geben Sie für alle Aufgaben einen Text ein.")
        return false
      }
      const promises = []

      promises.push(axios.post(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`, {
        duration: this.value.duration,
        tags: this.value.tags,
        title: this.value.title,
        visible: this.value.visible,
        foreign_id: this.value.foreign_id,
      }).catch(() => {
        alert("Die Aufgabenliste konnte leider nicht gespeichert werden. Bitte versuchen Sie es später erneut.")
      }))

      const itemUpdates = this.todolistItems.map(item => {
        return {
          id: item.id,
          position: item.position,
          title: item.title,
          description: item.description,
        }
      })
      promises.push(axios.post(`/backend/api/v1/todolists/${this.value.foreign_id}/update-items`, {
        todolistItems: itemUpdates,
      }).catch(() => {
        alert("Die Aufgabenliste konnte leider nicht gespeichert werden. Bitte versuchen Sie es später erneut.")
      }))

      return Promise.all(promises)
    },
    addTodolistItem() {
      axios.post(`/backend/api/v1/todolists/${this.value.foreign_id}/items`)
        .then((response) => {
          this.todolistItems.push(response.data.todolistItem)
        })
        .catch(() => {
          alert("Der Inhalt konnte leider nicht hinzugefügt werden. Bitte probieren Sie es später erneut.")
        })
    },
  },
  components: {
    TodolistItemList,
    TagSelect,
  },
}
</script>

<style lang="scss" scoped>
#app .s-addButton {
  height: 48px;
  width: 48px;
}
</style>
