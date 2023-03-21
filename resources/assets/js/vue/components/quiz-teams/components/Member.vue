<template>
  <tr>
    <td>
      {{ member.fullName }}
    </td>
    <td v-if="showEmails('quizteams')">
      {{ member.email }}
    </td>
    <td class="px-1 text-sm-right">
      <v-btn @click="deleteMember" flat icon>
        <v-icon small>
          delete
        </v-icon>
      </v-btn>
    </td>
  </tr>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: ['value'],
  computed: {
    ...mapGetters({
      showEmails: 'app/showEmails',
    }),
    member: {
      get() {
        return this.value
      },
      set(member) {
        this.$emit('input', member)
      },
    },
  },
  methods: {
    deleteMember() {
      if(confirm('Möchten Sie diesen Benutzer entfernen?')) {
        this.$emit('delete', this.member)
      }
    },
  },
}
</script>
