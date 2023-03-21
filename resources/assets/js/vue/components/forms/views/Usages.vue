<template>
  <div v-if="form" class="pa-4">
    <div class="subheading">Wo wird dieses Formular verwendet?</div>
    <v-data-table
      :headers="headers"
      :items="form.usages"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="usageCount"
      class="elevation-1 mt-3"
      item-key="id">
      <tr
        @click="editUsage(props.item)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.title }}
        </td>
        <td>
          Kurs
        </td>
      </tr>
      <template slot="no-data">
        <v-alert
          :value="true"
          type="info">
          Dieses Formular wird aktuell nicht verwendet
        </v-alert>
      </template>
    </v-data-table>
  </div>
</template>

<script>
export default {
  props: ['form'],
  data() {
    return {
      headers: [
        {
          text: "Name",
          value: "title",
          sortable: false,
        },
        {
          text: "Typ",
          value: "type",
          sortable: false,
        },
      ],
    }
  },
  computed: {
    usageCount() {
      return this.form.usages.length
    },
  },
  methods: {
    editUsage(usage) {
      window.location.href = `/courses#/courses/${usage.id}/general`
    },
  },
}
</script>
