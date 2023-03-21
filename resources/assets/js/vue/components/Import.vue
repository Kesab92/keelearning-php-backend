<template>
  <div>
    <div class="c-moduleIntro">
      <h1 class="c-moduleIntro__heading">
        Import
      </h1>
      <div class="c-moduleIntro__description">
        Importieren Sie Benutzer und Fragen per Excel oder CSV Datei.
      </div>
      <div class="c-moduleIntro__links">
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233462-import"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung öffnen
        </v-btn>
      </div>
    </div>
    <v-container class="grid-list-lg">
      <v-layout>
        <v-flex
          v-if="questions"
          md4
          sm6
          xs12>
          <v-card>
            <v-img
              height="300px"
              src="/img/questions-backdrop.jpg"
            >
              <v-container
                fill-height
                fluid>
                <v-layout fill-height>
                  <v-flex
                    align-end
                    flexbox
                    xs12>
                    <span class="headline white--text">Fragen</span>
                  </v-flex>
                </v-layout>
              </v-container>
            </v-img>

            <v-card-text class="main-text">
              Hier können Sie neue Fragen importieren.<br>
              Überarbeitete Fragen können Sie im <a href="/questions">Fragenpool</a> wieder importieren.
            </v-card-text>

            <v-alert
              v-if="currentLanguage !== defaultLanguage"
              :value="true"
              color="#b3b3b3">
              Fragen können nur in der Primärsprache importiert werden. Die Sekundärsprache "{{ currentLanguage }}" ist gerade aktiv. <a :href="`/setlang/${defaultLanguage}`">Zu "{{ defaultLanguage }}" wechseln</a>
            </v-alert>

            <v-card-actions v-if="currentLanguage === defaultLanguage">
              <v-btn
                href="/import/questions"
                color="primary">Neue Fragen importieren <v-icon right>arrow_right</v-icon></v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>

        <v-flex
          v-if="removeusers || users"
          md4
          sm6
          xs12>
          <v-card>
            <v-img
              height="300px"
              src="/img/users-backdrop.jpg"
            >
              <v-container
                fill-height
                fluid>
                <v-layout fill-height>
                  <v-flex
                    align-end
                    flexbox
                    xs12>
                    <span class="headline white--text">Benutzer</span>
                  </v-flex>
                </v-layout>
              </v-container>
            </v-img>

            <v-card-text class="main-text">
              Hier können Sie Benutzer einladen oder mehrere Benutzer auf einmal löschen.
            </v-card-text>


            <v-card-actions v-if="users">
              <v-btn
                href="/import/users"
                color="primary">Neue Benutzer einladen <v-icon right>arrow_right</v-icon></v-btn>
            </v-card-actions>

            <v-card-actions v-if="removeusers">
              <v-btn
                href="/import/delete-users"
                color="primary">Benutzer löschen <v-icon right>arrow_right</v-icon></v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>

        <v-flex
          v-if="indexcards"
          md4
          sm6
          xs12>
          <v-card>
            <v-img
              height="300px"
              src="/img/indexcards-backdrop.jpg"
            >
              <v-container
                fill-height
                fluid>
                <v-layout fill-height>
                  <v-flex
                    align-end
                    flexbox
                    xs12>
                    <span class="headline white--text">Karteikarten</span>
                  </v-flex>
                </v-layout>
              </v-container>
            </v-img>

            <v-card-text class="main-text">
              Importieren Sie neue Karteikarten.
            </v-card-text>

            <v-card-actions>
              <v-btn
                href="/import/cards"
                color="primary">Karteikarten importieren <v-icon right>arrow_right</v-icon></v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>
      </v-layout>

      <LastImports />

    </v-container>
  </div>
</template>

<script>
  import LastImports from './partials/imports/LastImports'
  export default {
    props: {
      indexcards: {
        type: Boolean,
      },
      questions: {
        type: Boolean,
      },
      removeusers: {
        type: Boolean,
      },
      users: {
        type: Boolean,
      },
      currentLanguage: {
        type: String,
      },
      defaultLanguage: {
        type: String,
      },
    },
    data() {
      return {
        showQuestionImportExplanation: false,
        showUserImportExplanation: false,
        showUserSyncExplanation: false,
        showUserRemoveExplanation: false,
        showIndexcardsImportExplanation: false,
      }
    },
    components: {
      LastImports,
    },
  }
</script>

<style lang="scss" scoped>
  #app .v-card__actions ::v-deep .v-btn.primary {
    width: 100%;
  }
  #app .v-card__actions ::v-deep .primary .v-btn__content {
    justify-content: space-between;
    padding: 0 20px;
  }

  #app .main-text {
    height: 90px;
  }
</style>
