<template functional>
  <div>
    <v-alert
      :value="!!props.warnings.powerless_admins"
      type="warning"
      class="mb-2">
      <router-link
        :to="{
          name: 'users.index',
          params: {
            config: JSON.stringify({
              filter: 'powerless_admins'
            }),
          }
        }"
      >
        {{ props.warnings.powerless_admins }}
        Administrator{{ props.warnings.powerless_admins > 1 ? 'en' : '' }}
      </router-link>
      {{ props.warnings.powerless_admins > 1 ? 'haben' : 'hat' }}
      keine Berechtigungen
    </v-alert>
    <v-alert
      :value="!!props.warnings.failed_login"
      type="warning"
      class="mb-2">
      <router-link
        :to="{
          name: 'users.index',
          params: {
            config: JSON.stringify({
              filter: 'failed_login'
            }),
          }
        }"
      >
        {{ props.warnings.failed_login }}
        Benutzer
      </router-link>
      {{ props.warnings.failed_login > 1 ? 'sind' : 'ist' }}
      gesperrt und
      {{ props.warnings.failed_login > 1 ? 'brauchen' : 'braucht' }}
      ein neues Passwort.
    </v-alert>
    <v-alert
      :value="!!props.warnings.without_category"
      type="warning"
      class="mb-2">
      <router-link
        :to="{
          name: 'users.index',
          params: {
            config: JSON.stringify({
              filter: 'without_category'
            }),
          }
        }"
      >
        {{ props.warnings.without_category }}
        Benutzer
      </router-link>
      {{ props.warnings.without_category > 1 ? 'haben' : 'hat' }}
      keine spielbare Kategorie zugewiesen und
      {{ props.warnings.without_category > 1 ? 'können' : 'kann' }}
      nicht am Quiz teilnehmen.
    </v-alert>
  </div>
</template>
