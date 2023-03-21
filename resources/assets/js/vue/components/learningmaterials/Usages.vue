<template>
  <div v-if="material" class="pa-4">
    <div class="subheading">Wo wird diese Datei außerhalb der Mediathek verwendet?</div>
    <v-data-table
      :headers="headers"
      :items="material.usages"
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
          Diese Datei wird aktuell nicht außerhalb der Mediathek verwendet
        </v-alert>
      </template>
    </v-data-table>
  </div>
</template>

<script>
export default {
  props: ['material'],
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
      return this.material.usages.length
    },
  },
  methods: {
    editUsage(usage) {
      this.$router.push({
        name: 'courses.edit.general',
        params: {
          courseId: usage.id,
        },
      })
    },
  },
}
</script>
