<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-slot:default>
        <v-btn
          color="primary"
          :disabled="!reportData.settings.length"
          :href="exportLink"
          target="_blank"
        >
          Herunterladen
        </v-btn>
      </template>
    </details-sidebar-toolbar>
  </div>
</template>

<script>
export default {
  props: [
    'reportData',
    'exportLink',
  ],
}
</script>
