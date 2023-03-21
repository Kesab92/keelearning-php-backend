<template>
  <details-sidebar
    :root-url="{
      name: 'suggested-questions.index',
    }"
    :drawer-open="typeof $route.params.suggestedQuestionId !== 'undefined'"
    data-action="suggestedQuestions/loadSuggestedQuestion"
    :data-getter="(params) => $store.getters['suggestedQuestions/suggestedQuestion'](params.suggestedQuestionId)"
    :data-params="{suggestedQuestionId: $route.params.suggestedQuestionId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: suggestedQuestion, refresh }">
      <router-view
        :suggestedQuestion="suggestedQuestion"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: suggestedQuestion }">
      {{ suggestedQuestion.title }}
    </template>
    <template v-slot:headerExtension="{ data: suggestedQuestion }">
      Erstellt am {{ suggestedQuestion.created_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(suggestedQuestion) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'suggested-questions.edit.general',
            params: {
              suggestedQuestionId: suggestedQuestion.id,
            },
          },
        },
      ]
    }
  }
}
</script>
