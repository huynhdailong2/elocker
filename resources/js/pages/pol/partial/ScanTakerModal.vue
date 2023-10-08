<template>
  <modal
    :name="name"
    :width="'550'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">{{ isIssuing ? 'Issuing' : 'Replenish' }} POL</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="scan-personnel" v-if="step === STEP.SCAN_USER">
        <template v-if="userScan">
          <div class="info">
            <div>
              <div class="label">Name</div>
              <input type="text" class="input" disabled :value="userScan.login_name" >
            </div>
            <div>
              <div class="label">Employee ID</div>
              <input type="text" class="input" disabled :value="userScan.card_id" >
            </div>
            <div class="desc">Item(s) will be taken to the above-mentioned personnel.</div>
          </div>
        </template>
        <template v-else>
          <div class="desc">Please input personnel ID taking over the item(s)</div>
          <div class="input-search">
            <input type="text" class="input" v-model="inputSearch" ref="userCardNo" placeholder="User's Card Number">
          </div>
        </template>
        <button class="btn btn-primary" @click.stop="onClickSubmit" v-if="userScan">Submit</button>
      </div>

      <div class="printable" v-if="step === STEP.PRINTABLE">
        <p class="mb-3">Click the button below to print this transaction.</p>
        <button class="btn btn-primary" @click.stop="onClickPrint">Print</button>
      </div>

      <issued-pol-print
        :data="data"
        ref="issuePolPrint"
        v-if="isIssuing" />

      <replenished-pol-print
        :data="data"
        ref="replenishPolPrint"
        v-if="isReplenish" />
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  text-align: center;
  .scan-personnel {
    text-align: center;
    margin-bottom: 40px;
    .input-search {
      margin: 10px 20px;
    }
    .btn {
      margin-top: 10px;
    }
    .info {
      .label {
        width: 100px;
        display: inline-block;
      }
      input {
        width: 50%;
        margin: 10px 20px;
      }
    }
  }
  .btn-primary {
    padding-left: 30px;
    padding-right: 30px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, debounce, cloneDeep } from 'lodash'
import IssuedPolPrint from './IssuedPolPrint'
import ReplenishedPolPrint from './ReplenishedPolPrint'
import Const from 'common/Const'
import { mapState } from 'vuex';

const STEP = {
  SCAN_USER: 'scan-user',
  PRINTABLE: 'printable'
}

export default {
  components: {
    IssuedPolPrint,
    ReplenishedPolPrint
  },

  props: {
    name: String,
    default: 'ScanTakerModal'
  },

  data () {
    return {
      inputSearch: null,
      userScan: null,
      step: STEP.SCAN_USER,
      STEP,
      callback: null,
      data: [],
      type: null
    }
  },

  computed: {
    ...mapState(['user']),

    isIssuing () {
      return this.type === Const.POL_ACTION.ISSUE
    },

    isReplenish () {
      return this.type === Const.POL_ACTION.REPLENISH
    },
  },

  watch: {
    inputSearch: debounce(function () {
      this.getUserInfoByCardId()
    }, 300)
  },

  mounted () {
    this.$nextTick(() => {
      this.$refs.userCardNo && this.$refs.userCardNo.focus()
    })
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.type = params?.type
      this.callback = params?.callback
      this.data = cloneDeep(params?.data || [])
    },

    close () {
      this.step = STEP.SCAN_USER
      this.userScan = null

      this.$modal.hide(this.name)
    },

    async onClickSubmit () {
      if (!this.callback || !this.userScan) {
        return
      }

      this.data = chain(this.data)
        .filter(item => !!item.quantity)
        .map(item => {
          return {
            ...item,
            receiver_id: this.userScan.id,
            issue_by: this.user.login_name,
            issue_to: this.userScan.login_name,
            requester_id: this.userScan.id,
            receiver_name: this.user.login_name,
            requester_name: this.userScan.login_name
          }
        })
        .value()
      await this.callback(this.data)

      this.step = STEP.PRINTABLE
    },

    onClickPrint () {
      this.$nextTick(() => {
        switch (this.type) {
          case Const.POL_ACTION.ISSUE:
            return this.$refs.issuePolPrint.print()
          case Const.POL_ACTION.REPLENISH:
            return this.$refs.replenishPolPrint.print()
        }
      })
    },

    getUserInfoByCardId () {
      rf.getRequest('UserRequest').getUserInfoByCardId({ card_id: this.inputSearch })
        .then(res => {
          this.userScan = res.data;
        })
    }
  }
}
</script>
