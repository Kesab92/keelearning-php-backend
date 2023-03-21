<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="quizTeams"
      :loading="isLoading"
      :rows-per-page-items="[50, 100, 200]"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editQuizTeam(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.name }}
        </td>
        <td>
          {{ props.item.members.length }}
        </td>
        <td>
          {{ props.item.id }}
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  data() {
    return {
      headers: [
        {
          text: "Name",
          value: "name",
        },
        {
          text: "Mitglieder",
          value: "members",
          sortable: false,
        },
        {
          text: "ID",
          value: "id",
          width: "90px",
        },
      ],
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      quizTeamCount: 'quizTeams/quizTeamCount',
      quizTeams: 'quizTeams/quizTeams',
      isLoading: 'quizTeams/listIsLoading'
    }),
  },
  methods: {
    editQuizTeam(quizTeamId) {
      this.$router.push({
        name: 'quizTeams.edit.general',
        params: {
          quizTeamId: quizTeamId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('quizTeams/loadQuizTeams')
    },
  },
}
</script>
