<template>
  <CertificateDesigner
    v-if="!isLoading"
    :certificate-id="certificateId"
    :foreign-id="test.id"
    :isReadonly="isReadonly"
    type="test"
    :showPassedPercentage="true"
    @saved="handleSave">
    <template slot="header">
      <a :href="'/tests/' + test.id">
        <v-btn
          flat
          icon>
          <v-icon>arrow_back</v-icon>
        </v-btn>
      </a>
      <v-toolbar-title>{{ test.name }}</v-toolbar-title>
      <v-spacer/>
    </template>
  </CertificateDesigner>
</template>

<script>
import {mapGetters} from 'vuex'
import CertificateDesigner from './CertificateDesigner.vue'

export default {
  props: ['testId'],
  data() {
    return {
      isLoading: true,
      test: null,
      certificateId: null,
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['tests-edit']
    },
  },
  methods: {
    loadData() {
      this.isLoading = true
      axios.get('/backend/api/v1/tests/' + this.testId).then(response => {
        this.test = response.data.test
        if(response.data.certificate) {
          this.certificateId = response.data.certificate.id
        }
      }).catch(() => {
        alert('Die Daten für das Zertifikat konnten leider nicht abgerufen werden. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isLoading = false
      })
    },
    handleSave(certificate) {
      this.certificateId = certificate.id
    },
  },
  components: {
    CertificateDesigner,
  }
}
</script>
