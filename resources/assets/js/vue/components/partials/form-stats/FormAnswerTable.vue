<template>
  <v-data-table
    :headers="headers"
    :items="answers"
    :loading="isLoading"
    :pagination.sync="paginationData"
    :rows-per-page-items="[50, 100, 200]"
    :total-items="answerCount"
    class="elevation-1 form-answers-table"
    item-key="id">
    <tr
      slot="items"
      slot-scope="props">
      <td v-if="showPersonalData">
        {{ props.item.user.username }}
        <div
          class="grey--text"
          v-if="props.item.user.email">
          {{ props.item.user.email }}
        </div>
      </td>
      <td
        v-for="field in fields"
        :key="field.id">
        {{ getAnswerField(field, props.item.fields) }}
      </td>
    </tr>
    <template slot="no-data">
      <v-alert
        :value="true"
        type="info"
        v-show="(!answers || answers.length === 0) && !isLoading">
        Es wurden keine Benutzer gefunden.
      </v-alert>
    </template>
    <template slot="actions-prepend">
      <div class="page-select">
        Page:
        <v-select
          :items="pageSelectOptions"
          v-model="pagination.page"
          class="pagination" />
      </div>
    </template>
  </v-data-table>
</template>

<script>
export default {
  props: {
    answers: {
      type: Array,
      required: true,
    },
    answerCount: {
      type: [Number, null],
    },
    fields: {
      type: Array,
      required: true,
    },
    isLoading: {
      type: Boolean,
      required: false,
      default: false,
    },
    pagination: {
      type: Object,
      required: true,
    },
    showPersonalData: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data () {
    return {
      paginationData: null,
    }
  },
  created() {
    this.paginationData = JSON.parse(JSON.stringify(this.pagination))
  },
  watch: {
    paginationData: {
      handler() {
        this.$emit('update:pagination', this.paginationData)
      },
      deep: true,
    },
  },
  computed: {
    headers() {
      const headers = []
      if (this.showPersonalData) {
        headers.push({
          text: 'Name',
          value: 'username',
          sortable: false,
        })
      }
      this.fields.forEach(field => {
        headers.push({
          text: field.title,
          sortable: false,
        })
      })
      return headers
    },
    pageSelectOptions() {
      if (!this.answerCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.answerCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
  },
  methods: {
    getAnswerField(field, answerFields) {
      const answerField = answerFields.find(answerField => {
        return answerField.form_field_id === field.id
      })

      if(answerField) {
        return answerField.answer
      }
      return ''
    },
  },
}
</script>

<style lang="scss">
#app .form-answers-table {
  .v-datatable__actions__select {
    max-width: 180px;
  }

  .page-select {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: 14px;
    height: 58px; // IE11 fix
    margin-bottom: -6px;
    color: rgba(0, 0, 0, 0.54);

    // IE11 fixes
    .v-select__slot, .v-select__selections {
      height: 32px;
    }

    .v-select {
      flex: 0 1 0;
      margin: 13px 0 13px 34px;
      font-size: 12px;
    }
  }
}
</style>
