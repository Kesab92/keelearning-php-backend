<template>
  <ModuleIntro>
    <template v-slot:title>
      Quiz-Battle Statistik
    </template>
    <template v-slot:description>
      Informationen zu den Quiz-Spielen der Benutzer, Fragen und Kategorien. Der XLS-Download enthält alle Daten.
    </template>
  </ModuleIntro>
</template>
<script>
import ModuleIntro from "../../partials/global/ModuleIntro"

export default {
  components: {
    ModuleIntro,
  }
}
</script>
