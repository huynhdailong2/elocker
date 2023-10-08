<template>
  <modal
    :name="name"
    :width="'550'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Issuing Cart</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="scan-personnel" v-if="step === STEP.SCAN_USER">
        <template v-if="user">
          <div class="info">
            <div>
              <div class="label">Name</div>
              <input type="text" class="input" placeholder="User's Card Number" disabled :value="user.login_name" >
            </div>
            <div>
              <div class="label">Employee ID</div>
              <input type="text" class="input" disabled :value="user.card_id" >
            </div>
            <div class="desc">Item(s) will be taken to the above-mentioned personnel.</div>
          </div>
        </template>
        <template v-else>
          <div class="desc">Please scan personnel ID taking over the item(s)</div>
          <div class="input-search"><input type="text" class="input" v-model="inputSearch" ref="userCardNo"></div>
        </template>
        <button class="btn btn-primary" @click.stop="onClickSubmit" v-if="user">Submit</button>
      </div>

      <div class="printable" v-if="step === STEP.PRINTABLE">
        <p class="mb-3">Click the button below to print this transaction.</p>
        <button class="btn btn-primary" @click.stop="onClickPrint">Print</button>
      </div>

      <issue-card-print
        :jobCard="jobCard"
        :data="data"
        ref="issueCardPrint" />
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
import IssueCardPrint from './IssueCardPrint'

const STEP = {
  SCAN_USER: 'scan-user',
  PRINTABLE: 'printable'
}

export default {
  components: {
    IssueCardPrint
  },

  props: {
    name: String,
    default: 'ScanTakerModal'
  },

  data () {
    return {
      inputSearch: null,
      user: null,
      step: STEP.SCAN_USER,
      STEP,
      callback: null,
      data: [],
      jobCard: null,
      torqueAreas: [],
    }
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
      this.callback = params?.callback
      this.data = cloneDeep(params?.data || [])
      this.jobCard = params?.jobCard
      this.torqueAreas = params?.torqueAreas || []
    },

    close () {
      this.step = STEP.SCAN_USER
      this.user = null

      this.$modal.hide(this.name)
      return this.$router.push('/inventory')
    },

    async onClickSubmit () {
      if (!this.callback || !this.user) {
        return
      }

      this.data = chain(this.data)
        .map(item => {
          return { ...item, taker_id: this.user.id, taker_name: this.user.login_name }
        })
        .value()
      await this.callback(this.data)

      this.step = STEP.PRINTABLE
    },

    onClickPrint () {
      chain(this.data)
        .each(record => {
          const torque = chain(this.torqueAreas)
            .filter(item => item.id === record.torque_wrench_area_id)
            .head()
            .value()

          this.$set(record, 'torque_area', torque ? torque.area : null)
          this.$set(record, 'torque_value', torque ? parseInt(torque.torque_value) : null)
        })
        .value()

      this.$nextTick(() => {
        this.$refs.issueCardPrint.print();
      })
    },

    getUserInfoByCardId () {
      rf.getRequest('UserRequest').getUserInfoByCardId({ card_id: this.inputSearch })
        .then(res => {
          this.user = res.data;
        })
    }
  }
}
</script>
