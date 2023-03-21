<template>
  <v-data-table
    :headers="tableHeaders"
    :items="items"
    class="elevation-1"
    :rows-per-page-items="rowsPerPage"
  >
    <template slot="headerCell" slot-scope="props">
      <v-select
        :items="availableHeadersSelect"
        box
        class="header-select"
        hide-details
        single-line
        :class="{'not-selected': !props.header.value}"
        @change="changeHeader(props.header.idx, $event)"
        v-model="props.header.value"
      />
    </template>
    <template slot="items" slot-scope="props">
      <td
        :key="idx"
        :class="{'header-not-selected': !headers[idx]}"
        v-for="(entry, idx) in props.item"
      >
        {{ entry }}
      </td>
      <td v-if="isQuestionImport">
        <QuestionPreview
          :question="props.item"
          :headers="headers"
          :available-headers="availableHeaders"
          :type="type"
        />
      </td>
    </template>
  </v-data-table>
</template>

<script>
  import QuestionPreview from "./QuestionPreview"
  export default {
    components: {QuestionPreview},
    props: ["headers", "availableHeaders", "items", "isQuestionImport", "type"],
    data() {
      return {
        rowsPerPage: [10, 25, 50, {"text":"Alle","value":-1}],
      }
    },
    computed: {
      tableHeaders: {
        get () {
          let headers = []
          this.headers.forEach((header, idx)=> {
            headers.push({
              idx: idx,
              value: header,
              sortable: false
            })
          })
          return headers
        },
        set (newHeader) {
          // This isn't used, because we set the headers entries individually in the changeHeader method
        }
      },
      availableHeadersSelect () {
        let headers = [
          {
            text: 'Nicht zugewiesen',
            value: null,
          }
        ]
        Object.keys(this.availableHeaders).forEach(key => {
          headers.push({
            text: this.availableHeaders[key].title,
            value: key
          })
        })
        return headers
      }
    },
    methods: {
      changeHeader(idx, newValue) {
        this.$emit('setHeader', {
          idx: idx,
          newValue: newValue
        })
      }
    }
  }
</script>

<style lang="scss">
  #app .v-text-field.v-text-field--enclosed.header-select {
    .v-input__slot {
      min-height: 0 !important;
    }

    .v-input__append-inner {
      margin-top: 6px;
    }
  }

  #app .theme--light.v-text-field.not-selected > .v-input__control > .v-input__slot:before {
    border-color: #EF5350;
  }
</style>

<style lang="scss" scoped>
  .header-not-selected {
    color: #757575;
    background: #eee;
  }
</style>