<template>
  <div class="page">
    <div>
      <div class="title-page">
        <div class="text-center">Return Items</div>
      </div>

      <div class="block content" v-if="step === STEP.SCAN">
        <div class="text-center mt-3 mb-4">
          <div class="mb-2">Please input user's card details</div>
          <div>
            <input
              type="text"
              class="input"
              name="inputText"
              placeholder="Employee's Card"
              v-model.trim="inputUserCard"
              ref="inputText" >
            <button @click.stop="onClickUserInfoByCardId" type="button" class="btn btn-primary" style="margin-top: -5px;">Go</button>
          </div>
        </div>
      </div>

      <div class="block content" v-if="step === STEP.RETURNING">
        <div class="ic-back" @click.stop="onClickBack">
          <img src="/images/icons/icon-back2.svg" class="mr-2" width="25">
        </div>

        <div class="form-search">
          <label>Item P/N:</label>
          <div>
            <input
              type="text"
              class="input"
              name="inputText"
              placeholder="P/N"
              v-model.trim="inputText"
              ref="inputText" >
          </div>
          <button class="btn btn-primary">Search</button>
        </div>
        <issue-cards ref="issueCards" />
      </div>

    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  .ic-back {
    position: absolute;
    top: 40px;
    img {
      cursor: pointer;
    }
  }
  .form-search {
    display: flex;
    justify-content: center;
    align-items: baseline;
    margin-bottom: 40px;
    button {
      padding: 15px 40px;
      margin-top: 20px;
    }
  }
  .input {
    width: 300px;
    margin: 0 10px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, toLower, isEmpty, debounce } from 'lodash'
import IssueCards from './partials/IssueCards'

const STEP = {
  SCAN: 'scan',
  RETURNING: 'returning'
}

export default {
  components: {
    IssueCards
  },

  data () {
    return {
      inputUserCard: null,
      inputText: null,
      spares: [],
      step: STEP.SCAN,
      STEP
    }
  },

  computed: {
    noData () {
      return isEmpty(this.filtered)
    },

    filtered () {
      return chain(this.spares)
        .filter(item => {
          const contains = (string, keyword) => {
            return toLower(string).includes(keyword)
          }
          const keyword = toLower(this.inputText)

          return contains(item.material_no, keyword) || contains(item.part_no, keyword)
            || contains(item.serial_no, keyword)
        })
        .value()
    }
  },

  watch: {
    // inputUserCard: debounce(function (newVal) {
    //   this.getUserInfoByCardId()
    // }, 300),

    inputText: debounce(function (newVal) {
      this.$refs.issueCards.filter(this.inputText)
    }, 300)
  },

  methods: {
    getUserInfoByCardId () {
      if (!this.inputUserCard) {
        return
      }

      rf.getRequest('UserRequest').getUserInfoByCardId({ card_id: this.inputUserCard })
        .then(res => {
          this.step = STEP.RETURNING
          this.$nextTick(() => {
            this.$refs.issueCards.getSparesReturn({user_id: res.data.id})
            this.$refs.issueCards.setUserId(res.data.id)
          })
        })
    },

    onClickBack () {
      this.inputUserCard = null
      this.step = STEP.SCAN
    },

    onClickUserInfoByCardId() {
      this.getUserInfoByCardId()
    }
  }
}
</script>
