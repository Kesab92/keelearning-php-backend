<template>
  <div class="screen">
    <div class="search-bar-container">
      <h1>keelearning<span>Helpdesk</span></h1>
      <div>- Fachkundige Unterstützung für Ihren Erfolg -</div>
      <form @submit.prevent="submitSearch">
        <input type="text" v-model="keyword" placeholder="Suchen..." />
        <help-result-modal
          :results="results"
          :keyword="keyword"
          :loading="loading"
        />
      </form>

      <div class="image-container">
        <img src="/img/Helpdesk_keelearning.png" />
      </div>
    </div>
    <div class="information-container">
      <div class="heading-container">
        <h1>Hilfe-Service</h1>
        <p>Stöbern Sie in der Wissensdatenbank, den Videotutorials oder schreiben Sie uns</p>
      </div>
      <div class="menu-container">
        <div id="faq" class="menu-item">
            <div class="half-circle">
              <img src="/img/FaQ.png" />
            </div>
            <div>
              <div class="short-info">
                <div class="heading">FAQs</div>
                <i v-if="countData">{{ countData.faq }} Artikel</i>
              </div>
              <div class="description">Vielleicht helfen Ihnen bereits Antworten zu Fragen, die auch andere User hatten, die wichtigsten Fragen auf einen Blick.</div>
              <div>
                <a href="/help/faq">
                  Weiter
                  <v-icon>arrow_right_alt</v-icon>
                </a>
              </div>
            </div>
        </div>
        <div id="knowledge" class="menu-item">
          <div class="half-circle">
            <img src="/img/Knowledge.png" />
          </div>
          <div>
            <div class="short-info">
              <div class="heading">Knowledgebase</div>
              <i v-if="countData">{{ countData.knowledgeArticles }} Artikel / {{ countData.knowledgeCategories }} Kategorien</i>
            </div>
            <div class="description">Produktdokumentation und Trainingsvideos, die Ihnen vermitteln, wie keelearning und das Redaktionssystem funktionieren.</div>
            <div>
              <a href="/help/knowledge?page=1">
                Weiter
                <v-icon>arrow_right_alt</v-icon>
              </a>
            </div>
          </div>
        </div>
        <div id="support" class="menu-item">
          <div class="half-circle">
            <img src="/img/Support.png" />
          </div>
          <div>
            <div class="short-info">
              <div class="heading">Support</div>
            </div>
            <div v-if="supportInfo" class="description">
              Schreiben Sie an {{ supportInfo.email }} oder rufen Sie uns an unter {{ supportInfo.phone }}.
            </div>
            <div>
              <a v-if="supportInfo" :href="'mailto:'+ supportInfo.email">
                <div>Support kontaktieren</div>
                <v-icon>arrow_right_alt</v-icon>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        keyword: null,
        countData: null,
        supportInfo: null,
        results: null,
        loading: false,
      }
    },
    created() {
      axios.get('/backend/api/v1/helpdesk/counts').then(response => {
        if (response.data.success) {
          this.countData = response.data.data
        }
      })
      axios.get('/backend/api/v1/helpdesk/support-info').then(response => {
        if (response.data.success) {
          this.supportInfo = response.data.data
        }
      })
    },
    methods: {
      submitSearch() {
        if (this.keyword && this.keyword.length > 0) {
          this.loading = true
          axios.get('/backend/api/v1/helpdesk/' + this.keyword + '/query').then(response => {
            if (response.data.success) {
              this.results = response.data.data
            }
            this.loading = false
          })
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
  $faq-color: #7f59be;
  $knowledge-color: #de693d;
  $support-color: #17c485;

  #app {
    .screen {
      width: 100%;
      text-align: center;

      .information-container {
        width: 100%;

        .heading-container {
          padding-top: 20px;

          p {
            margin-top: 20px;
            color: #aaa;
          }
        }

        .menu-container {
          width: 100%;
          display: flex;
          padding: 10px 20%;
          margin-top: 70px;

          .menu-item {
            padding: 20px;
            margin: 0 1.5%;
            background: white;
            position: relative;
            width: calc(30% - 2px);
            border: 1px solid black;

            .half-circle {
              width: 80px;
              height: 40px;
              background-color: white;
              border-top-left-radius: 92px;
              border-top-right-radius: 92px;
              border: 2px solid white;
              position: absolute;
              border-bottom: 0;
              top: -40px;
              left: 0;
              right: 0;
              margin: auto;

              img {
                margin-top: 15px;
                width: 45px;
                height: 45px;
              }
            }

            .heading {
              font-size: 18px;
              font-weight: bold;
              padding-bottom: 20px;
            }

            .short-info, .description {
              margin-top: 40px;
            }

            a {
              line-height: 28px;
              margin-top: 40px;
              font-weight: bold;
              display: flex;
              flex-direction: row;
              color: black;
              align-items: center;
              justify-content: center;
            }
          }
        }

        #faq {
          border: 1px solid $faq-color;

          .half-circle {
            border-left: 1px solid $faq-color;
            border-right: 1px solid $faq-color;
            border-top: 1px solid $faq-color;
          }
        }

        #knowledge {
          border: 1px solid $knowledge-color;

          .half-circle {
            border-left: 1px solid $knowledge-color;
            border-right: 1px solid $knowledge-color;
            border-top: 1px solid $knowledge-color;
          }
        }

        #support {
          border: 1px solid $support-color;

          .half-circle {
            border-left: 1px solid $support-color;
            border-right: 1px solid $support-color;
            border-top: 1px solid $support-color;
          }
        }
      }

      .search-bar-container {
        width: 100%;
        height: 450px;
        position: relative;
        overflow: hidden;
        background: rgba(36, 165, 255, .2);

        h1 {
          padding-top: 50px;
          padding-bottom: 20px;

          span {
            font-weight: bold;
          }
        }

        input {
          width: 45%;
          padding: 6px;
          margin-top: 40px;
          border-radius: 3px;
          border: 1px solid black;
          background: white;
        }

        .image-container {
          bottom: -10px;
          width: 100%;
          margin: auto;
          position: absolute;

          img {
            width: 45%;
            max-height: 225px;
          }
        }
      }
    }
  }

</style>