<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="tests"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="testsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editTest(props.item.id)"
        class="clickable s-tests__row"
        slot="items"
        slot-scope="props">
        <td class="pa-2 pr-0">
          <img
            v-if="props.item.cover_image_url"
            class="s-tests__coverImage"
            :src="props.item.cover_image_url"/>
        </td>
        <td>
          {{ props.item.name }}
        </td>
        <td>
          <v-chip
            :key="`${props.item.id}-${tag.id}`"
            disabled
            small
            v-for="tag in props.item.tags">
            {{ tag.label }}
          </v-chip>
        </td>
        <td>
          {{ modeName(props.item.mode) }}
        </td>
        <td>
          {{ props.item.active_until | dateTime }}
        </td>
        <td>
          <v-icon
            v-if="props.item.certificateTemplateExists"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
        <td>
          <v-icon
            v-if="props.item.remindersExist"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
        <td>
          {{ props.item.id }}
        </td>
        <td
          v-if="myRights['tests-stats']">
          <v-btn
            :href="`/tests/${props.item.id}/results`"
            flat>
            <v-icon left>trending_up</v-icon>
            Statistiken
          </v-btn>
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";
import constants from "../../logic/constants";

export default {
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      testsCount: 'tests/testsCount',
      tests: 'tests/tests',
      isLoading: 'tests/listIsLoading',
      myRights: 'app/myRights',
    }),
    pagination: {
      get() {
        return this.$store.state.tests.pagination
      },
      set(data) {
        this.$store.commit('tests/setPagination', data)
      },
    },
    headers() {
      let headers = [
        {
          text: "",
          value: "image",
          width: "110px",
          sortable: false,
        },
        {
          text: "Name",
          value: "name",
          sortable: false,
        },
        {
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
        },
        {
          text: "Typ",
          value: "mode",
        },
        {
          text: "Läuft bis",
          value: "active_until",
          width: "280px",
        },
        {
          text: "Zertifikat",
          value: "certificateTemplateExists",
          sortable: false,
        },
        {
          text: "Eskalationsmanagement",
          value: "remindersExist",
          sortable: false,
        },
        {
          text: "ID",
          value: "id",
          width: "90px",
        }]

      if (this.myRights['tests-stats']) {
        headers.push({
          text: "",
          value: "id",
          sortable: false,
          width: "120px",
        })
      }

      return headers
    }
  },
  methods: {
    editTest(testId) {
      window.location.href = `/tests/${testId}`
    },
    loadData() {
      this.$store.dispatch('tests/loadTests')
    },
    modeName(val) {
      if (val === constants.TEST.MODE_QUESTIONS) {
        return 'statisch'
      }
      return 'zufällige Fragen'
    },
  },
}
</script>

<style scoped>
#app .s-tests__row td {
  height: 87px;
}

#app .s-tests__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
}
</style>
