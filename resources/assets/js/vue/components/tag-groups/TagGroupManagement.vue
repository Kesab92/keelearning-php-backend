<template>
    <div>
        <ModuleIntro>
          <template v-slot:title>
            TAG-Gruppen
          </template>
          <template v-slot:description>
            Spezialfeature – Ein Benutzer kann immer nur einem TAG einer TAG-Gruppe angehören.<br>
            Erlaubt das Erstellen von Dropdowns auf der Registrierungsseite.
          </template>
          <template v-slot:links>
            <v-btn
              flat
              color="primary"
              small
              href="https://helpdesk.keelearning.de/de/articles/5179402-uber-tag-gruppen"
              target="_blank"
            >
              <v-icon
                small
                class="mr-1">
                help
              </v-icon>
              Anleitung öffnen
            </v-btn>
          </template>
        </ModuleIntro>
        <v-snackbar
            :timeout="6000"
            :top="true"
            color="error"
            v-model="snackbar">
            {{ errorText }}
        </v-snackbar>
        <v-card>
            <v-card-title>
                <tag-group-modal
                    buttonTitle="TAG-Gruppe hinzufügen"
                    @success="loadTaggroups()"
                />
            </v-card-title>
            <v-data-table
                :headers="headers"
                :items="items"
                :loading="isLoading"
                :pagination.sync="pagination"
                :search="search"
                class="elevation-1"
                v-model="selected"
                item-key="id">
                <template slot="items" slot-scope="props">
                    <td>{{ props.item.name }}</td>
                    <td class="tag-group-buttons">
                        <tag-group-modal
                            buttonTitle="Bearbeiten"
                            :tagGroup="props.item"
                        />
                        <confirm-modal
                            @success="remove(props.item.id)"
                            message="Möchten Sie die TAG-Gruppe wirklich löschen?"
                            buttonTitle="Löschen"
                            buttonColor="error"
                        />
                    </td>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    import ModuleIntro from "../partials/global/ModuleIntro"

    export default {
      created() {
        this.loadTaggroups()
      },
      data() {
        return {
          items: [],
          search: null,
          selected: [],
          snackbar: false,
          errorText: null,
          isLoading: false,
          headers: [
            {
              text: "Name",
              value: "name",
              align: "left",
            },
            {
              text: "Aktionen",
              sortable: false,
              align: "right"
            },
          ],
          pagination: {
            sortBy: "created_at",
            descending: true,
            rowsPerPage: 25,
          },
        }
      },
      methods: {
        toggle(prop) {
          prop.selected = !prop.selected
        },
        loadTaggroups() {
          this.isLoading = true
          axios.get("/backend/api/v1/tag-groups")
            .then(response => {
              if (response.data.success) {
                this.items = response.data.data
              }
              this.isLoading = false
            })
            .catch(error => {
              this.isLoading = false
              this.showError("Error connecting to the server.")
            })
        },
        remove(id) {
          this.isLoading = true
          axios.post("/backend/api/v1/tag-groups/" + id + "/remove")
            .then(response => {
              if (response.data.success) {
                this.loadTaggroups()
                this.isLoading = false
              }
            })
            .catch(error => {
              this.showError(error)
              this.isLoading = false
            })
        },
        showError(error) {
          this.errorText = error
          this.snackbar = true
        },
      },
      components: {
        ModuleIntro,
      },
    }
</script>

<style lang="scss" scoped>
    .tag-group-buttons {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
    }
</style>
