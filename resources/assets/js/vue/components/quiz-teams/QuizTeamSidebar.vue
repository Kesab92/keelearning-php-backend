<template>
  <details-sidebar
    :root-url="{
      name: 'quizTeams.index',
    }"
    :drawer-open="typeof $route.params.quizTeamId !== 'undefined'"
    data-action="quizTeams/loadQuizTeam"
    :data-getter="(params) => $store.getters['quizTeams/quizTeam'](params.quizTeamId)"
    :data-params="{quizTeamId: $route.params.quizTeamId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: quizTeam, refresh }">
      <router-view
        :quizTeam="quizTeam"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: quizTeam }">
      {{ quizTeam.name }}
    </template>
    <template v-slot:headerExtension="{ data: quizTeam }">
      Erstellt am {{ quizTeam.created_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(quizTeam) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'quizTeams.edit.general',
            params: {
              quizTeamId: quizTeam.id,
            },
          },
        },
      ]
    }
  }
}
</script>
