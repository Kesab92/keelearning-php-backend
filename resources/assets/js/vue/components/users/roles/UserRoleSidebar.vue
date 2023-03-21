<template>
  <details-sidebar
    :root-url="{
      name: 'user-roles.index',
    }"
    :drawer-open="typeof $route.params.userRoleId !== 'undefined'"
    data-action="userRoles/loadUserRole"
    :data-getter="(params) => $store.getters['userRoles/userRole'](params.userRoleId)"
    :data-params="{userRoleId: $route.params.userRoleId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: userRole, refresh }">
      <router-view
        :userRole="userRole"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: userRole }">
      {{ userRole.name }}
    </template>
    <template v-slot:headerExtension="{ data: userRole }">
      Erstellt am {{ userRole.created_at | date }}<br>
      Bearbeitet am {{ userRole.updated_at | date }}<br>
      Admins mit dieser Rolle: {{ userRole.users.length }}
    </template>
  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(userRole) {
      return [
        {
          label: 'Übersicht',
          to: {
            name: 'user-roles.edit.general',
            params: {
              userRoleId: userRole.id,
            },
          },
        },
        {
          label: 'Rechte',
          to: {
            name: 'user-roles.edit.rights',
            params: {
              userRoleId: userRole.id,
            },
          },
        },
      ]
    },
  },
}
</script>
