<template>
  <div>
    <v-layout row>
      <v-flex xs6>
        <h1 class="mt-3 mb-3">Bewertungen</h1>
      </v-flex>
      <v-flex xs6 text-xs-right v-if="!loading" class="average-rating">
        <strong>Durchschnittliche Bewertung: {{ averageRating | decimals}} / 5.00</strong>
      </v-flex>
    </v-layout>
    <v-data-table
      :loading="loading"
      :headers="headers"
      :items="ratings"
      class="elevation-1"
      hide-actions>
      <template v-slot:items="props">
        <tr>
          <td>{{ props.item.rating | decimals }}</td>
          <td>
            <template v-if="props.item.tags && props.item.tags.length > 0">
              <v-chip
                label
                color="primary"
                text-color="white"
                small
                :key="tag"
                v-for="tag in props.item.tags">
                <v-icon left>label</v-icon>{{ tag }}
              </v-chip>
            </template>
            <template v-else>
              Keine TAGs vergeben.
            </template>
          </td>
          <td>{{ props.item.updated_at | dateTime }}</td>
        </tr>
      </template>
    </v-data-table>
  </div>
</template>

<script>
  export default {
    computed: {
      averageRating() {
        if (!this.ratings || this.ratings.length == 0) {
          return 0
        }

        return this.ratings
          .map(item => item.rating)
          .reduce((a, b) => {
            return a + b
          }) / this.ratings.length
      }
    },
    created() {
      this.fetchData()
    },
    data() {
      return {
        headers: [
          {
            text: 'Bewertung',
            value: 'rating'
          },
          {
            text: 'TAGs',
            value: 'tags'
          },
          {
            text: 'Bewertet am',
            value: 'updated_at'
          },
        ],
        ratings: [],
        loading: false,
      }
    },
    methods: {
      fetchData() {
        this.loading = true
        axios.get('/backend/api/v1/ratings').then(response => {
          if (response.data.success) {
            this.ratings = response.data.data
          }
          this.loading = false
        })
      }
    }
  }
</script>

<style lang="scss" scoped>
  #app {
    .average-rating {
      margin-top: 32px;
      margin-bottom: 16px;
    }
  }
</style>
