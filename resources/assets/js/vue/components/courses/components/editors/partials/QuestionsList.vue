<template>
  <div class="elevation-1">
    <v-toolbar
      flat
      color="white">
      <v-toolbar-title>Lernfragen</v-toolbar-title>
      <v-spacer/>
      <SearchQuestionsModal
        allow-learncards
        @add="addQuestions"
        @error="$emit('message', {type: 'error', message: $event})"
      >
        <v-btn
          :disabled="isReadonly"
          color="primary">
          <v-icon>
            add
          </v-icon>
          Fragen hinzufügen
        </v-btn>
      </SearchQuestionsModal>
    </v-toolbar>
    <v-data-table
      :headers="headers"
      :items="attachments"
      :pagination.sync="pagination"
      :rows-per-page-items="[15, 25, 50]"
    >
      <template v-slot:items="props">
        <td>{{ props.item.question.title }}</td>
        <td>{{ props.item.question.type }}</td>
        <td>{{ props.item.question.category }}</td>
        <td class="justify-center layout px-0">
          <v-icon
            v-if="!isReadonly"
            small
            @click="deleteItem(props.item)"
          >
            delete
          </v-icon>
        </td>
      </template>
      <template slot="no-data">
        Noch keine Fragen hinzugefügt
      </template>
    </v-data-table>
  </div>
</template>

<script>
  import SearchQuestionsModal from "../../../../partials/questions/SearchQuestionsModal"
  import {mapGetters} from "vuex";
  export default {
    props: ['attachments'],
    data() {
      return {
        dialog: false,
        search: null,
        pagination: {
          descending: false,
          rowsPerPage: 15,
          sortBy: "position",
        },
        headers: [
          {
            text: 'Frage',
            value: 'question.title',
            sortable: false,
          },
          {
            text: 'Typ',
            value: 'question.type',
            sortable: false,
          },
          {
            text: 'Kategorie',
            value: 'question.category',
            sortable: false,
          },
          {
            text: 'Löschen',
            value: 'id',
            sortable: false,
          }
        ],
      }
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      isReadonly() {
        return !this.myRights['courses-edit']
      },
    },
    methods: {
      deleteItem(item) {
        this.$emit('remove', item)
      },
      addQuestions(questions) {
        this.$emit('add', questions)
      },
      save() {
        this.dialog = false
      }
    },
    components: {
      SearchQuestionsModal,
    },
  }
</script>
