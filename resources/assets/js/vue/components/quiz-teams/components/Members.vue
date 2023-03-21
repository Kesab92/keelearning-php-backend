<template>
  <div>
    <v-toolbar
      flat
      color="white">
      <v-toolbar-title>Quiz-Team-Mitglieder</v-toolbar-title>
      <v-spacer/>
      <AddMember @addMember="addMember"/>
    </v-toolbar>
    <v-data-table
      :headers="headers"
      :items="members"
      class="elevation-1"
    >
      <template v-slot:no-data>
        <p class="text-xs-center my-2">
          Dieses Quiz-Team hat noch keine Mitglieder.
        </p>
      </template>
      <template v-slot:items="props">
        <Member
          v-model="props.item"
          :key="props.item.email"
          @delete="deleteMember"
        />
      </template>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import AddMember from './AddMember'
import Member from './Member'

export default {
  props: ['value'],
  computed: {
    ...mapGetters({
      showEmails: 'app/showEmails',
    }),
    members: {
      get() {
        return this.value
      },
      set(members) {
        this.$emit('input', members)
      },
    },
    headers() {
      const headers = [
        {
          text: 'Name',
          align: 'left',
          sortable: true,
          value: 'fullName',
        }]

      if (this.showEmails('quizteams')) {
        headers.push({
          text: 'E-Mail',
          align: 'left',
          sortable: true,
          value: 'email',
        })
      }

      headers.push({
        text: '',
        align: 'right',
        sortable: false,
      })

      return headers
    }
  },
  methods: {
    deleteMember(member) {
      const index = this.members.indexOf(member)
      this.$delete(this.members, index)
    },
    addMember(member) {
      if (this.members.some(u => u.user_id == member.id)) {
        return
      }
      this.members.push({
        id: member.id,
        fullName: member.fullName,
        email: member.email,
      })
    },
  },
  components: {
    AddMember,
    Member,
  },
}
</script>
