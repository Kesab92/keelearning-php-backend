<template>
  <div v-if="imports.length > 0">
    <h1 class="headline mt-5 mb-3">Letzte Importe</h1>
    <v-data-table
      :headers="headers"
      :items="imports"
      :hide-actions="true"
      class="elevation-1"
    >
      <template slot="items" slot-scope="props">
        <td>{{ humanName(props.item.type) }}</td>
        <td>{{ props.item.creator }}</td>
        <td>{{ humanDate(props.item.created_at) }}</td>
        <td class="text-xs-right">
          <v-progress-circular
            indeterminate
            v-if="props.item.status === 0"
            color="primary"
          ></v-progress-circular>
          <v-icon v-if="props.item.status === 1" color="green">check</v-icon>
          <v-tooltip v-if="props.item.status === 2" bottom>
            <template slot="activator">
              <v-icon style="cursor: help" color="red">error_outline</v-icon>
            </template>
            <span>Dieser Import ist leider fehlgeschlagen. Bitte kontaktieren Sie den Support.</span>
          </v-tooltip>
        </td>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import { format, parse } from 'date-fns'

  export default {
    data () {
      return {
        headers: [
          {
            text: 'Import',
            sortable: false,
          },
          {
            text: 'Gestartet von',
            sortable: false,
          },
          {
            text: 'Gestartet am',
            sortable: false,
          },
          {
            text: 'Status',
            sortable: false,
          },
        ],
        imports: []
      }
    },
    created() {
      this.fetchData()
    },
    methods: {
      humanName(type) {
        type = parseInt(type)
        let labels = {
          0: 'Benutzer importieren',
          1: 'Benutzer löschen',
          2: 'Fragen importieren',
          3: 'Karteikarten importieren',
        }
        if(typeof labels[type] === 'undefined') {
          return 'Import'
        }
        return labels[type]
      },
      humanDate(date) {
        return format(parse(date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'dd.MM.yyyy HH:mm:ss')
      },
      fetchData() {
        axios.get("/backend/api/v1/imports").then(response => {
          this.imports = response.data.imports
          window.setTimeout(this.fetchData, 3000)
        })
      },
    }
  }
</script>
