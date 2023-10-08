<template>
  <modal
    :name="name"
    :width="'500'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Checkout</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <template v-if="step === STEP.CHOOSE_ACTION">
        <p class="text-center">Please choose one of them:</p>
        <div class="choose-action">
          <div class="item" @click.stop="onClickChooseAction(ACTION.RETUNR_LOCKER)">
            <img src="/images/icons/inventory-mgmt.svg" />
            <div>Return to Locker</div>
          </div>
<!--          <div class="item" @click.stop="onClickChooseAction(ACTION.HAND_OVER)">-->
<!--            <img src="/images/icons/icon-hand-over.svg" />-->
<!--            <div>Hand Over</div>-->
<!--          </div>-->
        </div>
      </template>

      <template v-if="step === STEP.SUBMIT_RETURN_STORE">
        <div class="store">
          <div class="desc">Do you wanna perform?</div>
          <button class="btn btn-primary" @click.stop="onClickSubmit">Submit</button>
          <button class="btn btn-second" @click.stop="step = STEP.CHOOSE_ACTION">Back</button>
        </div>
      </template>

      <template v-if="step === STEP.SCAN_PERSONNEL_ID">
        <div class="scan-personnel">
          <template v-if="user">
            <div class="info">
              <div>
                <div class="label">Name</div>
                <input type="text" class="input" disabled :value="user.login_name" >
              </div>
              <div>
                <div class="label">Employee ID</div>
                <input type="text" class="input" disabled :value="user.card_id" >
              </div>
              <div class="desc">Item(s) will be handover to the above-mentioned personnel.</div>
            </div>
          </template>
          <template v-else>
            <div class="desc">Please scan personnel ID taking over the item(s)</div>
            <div class="input-search"><input type="text" class="input" v-model="inputSearch"></div>
          </template>
          <button class="btn btn-primary" @click.stop="onClickSubmit" v-if="user">Submit</button>
          <button class="btn btn-second" @click.stop="step = STEP.CHOOSE_ACTION">Back</button>
        </div>
      </template>
    </div>

    <return-items-print-modal :name="printModal" />
  </modal>
</template>
<style lang="scss" scoped>
.content {
  .choose-action {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 40px;
    .item {
      min-width: 175px;
      margin: 15px;
      padding: 25px;
      background-color: white;
      text-align: center;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
      cursor: pointer;
      img {
        width: 80px;
        margin-bottom: 20px;
      }
    }
  }
  .store {
    text-align: center;
    .btn {
      margin: 20px 10px 20px 0;
    }
  }
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
}
</style>
<script>
import rf from 'requestfactory'
import { chain, isEmpty, debounce } from 'lodash'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import ReturnItemsPrintModal from './ReturnItemsPrintModal'

const STEP = {
  CHOOSE_ACTION: 'choose-action',
  SUBMIT_RETURN_STORE: 'submit-return-store',
  SCAN_PERSONNEL_ID: 'scan-personnel-id'
}

const ACTION = {
  RETUNR_LOCKER: 'return_store',
  HAND_OVER: 'handover'
}

export default {
  components: {
    ReturnItemsPrintModal
  },

  props: {
    name: {
      type: String,
      default: 'checkout-return'
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      STEP,
      ACTION,
      step: STEP.CHOOSE_ACTION,
      action: null,
      user: null,
      spares: [],
      inputSearch: null,
      printModal: 'ReturnItemsPrintModal'
    }
  },

  watch: {
    inputSearch: debounce(function () {
      this.getUserInfoByCardId()
    }, 300)
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.spares = params.spares
    },

    close () {
      this.$modal.hide(this.name)
      this.user = null
      this.inputSearch = null
      this.step = STEP.CHOOSE_ACTION
      this.action = null
    },

    onClickChooseAction (action) {
      this.action = action

      switch (action) {
        case ACTION.RETUNR_LOCKER:
          this.step = STEP.SUBMIT_RETURN_STORE
          break;
        case ACTION.HAND_OVER:
          this.step = STEP.SCAN_PERSONNEL_ID
          break;
      }
    },

    onClickSubmit () {
      this.resetError()
      this.submitRequest()
        .then(res => {
          this.showSuccess('Returning Successful!')
          this.$modal.show(this.printModal, {
            data: this.spares,
            action: this.action === ACTION.HAND_OVER ? 'Hand Over' : 'Return to Store',
            receiver: this.user
          })
        })
        .catch(error => {
          this.processAndToastFirstError(error)
        })
    },

    submitRequest () {
      switch (this.action) {
        case ACTION.RETUNR_LOCKER:
          return rf.getRequest('SpareRequest').returnToStore(this.getParameters());
        case ACTION.HAND_OVER:
          return rf.getRequest('SpareRequest').returnByHandOver(this.getParameters());
      }
    },

    getParameters () {
      const spares = chain(this.spares)
        .map(item => {
          return {
            bin_id: item.bin_id,
            issue_card_id: item?.issue_card_id,
            return_spare_id: item?.return_spare_id,
            spare_id: item.spare_id,
            quantity: item.newQuantity,
            state: item.state
          }
        })
        .value()

      return { receiver_id: this.user ? this.user.id : null, spares }
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
