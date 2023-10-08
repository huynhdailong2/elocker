<template>
<div class="pol-table">
  <div class="table-scroller">
    <table>
      <thead>
        <th class="no">
          <label class="checkbox-container">No.
            <input type="checkbox" v-model="checkedAll" @click="onClickCheckedAll">
            <span class="checkmark"></span>
          </label>
        </th>
<!--        <th>Card Number</th>-->
        <th>Material Number</th>
        <th>Description</th>
        <th>Type</th>
        <th>Qty OH</th>
        <th v-if="isIssuingStep">Action</th>
      </thead>
      <tbody>
        <template v-if="noData">
          <tr>
            <td :colspan="numCols">There is no data.</td>
          </tr>
        </template>
        <template v-else>
          <tr v-for="(item, index) in data" v-if="item.visible">
            <td class="no" @click.prevent="onSelectBox(item)">
              <label class="checkbox-container">{{ index + 1 }}
                <input type="checkbox" v-model="item.is_checked">
                <span class="checkmark"></span>
              </label>
            </td>
<!--            <td><div>{{ item.card_number }}</div></td>-->
            <td><div>{{ item.material_number }}</div></td>
            <td><div>{{ item.description }}</div></td>
            <td><div>{{ item.type | upperFirst}}</div></td>
            <td><div>{{ +item.quantity_oh || 0 }}</div></td>
            <td v-if="isIssuingStep">
              <div class="form-input">
                <span class="circle" @click.stop="onClickDecrease(item)">-</span>
                <span class="number">{{ item.quantity }}</span>
                <span class="circle" @click.stop="onClickIncrease(item)">+</span>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  <div class="action">
    <template v-if="isIssuingStep">
      <button
        class="btn btn-second"
        @click.stop="onClickCancel">Cancel</button>

      <button
        class="btn btn-primary"
        :disabled="noIssuingData"
        @click.stop="onClickCheckout">Checkout</button>
    </template>

    <template v-else>
      <button
        class="btn btn-primary"
        :disabled="noSelectedData"
        @click.stop="onClickGotoCard">Go to Cart</button>
    </template>

  </div>

  <scan-taker-modal :name="scanTakerModal" />
</div>
</template>
<style lang="scss">
.pol-table {
  margin-top: 20px;
  .form-input {
    display: flex;
    justify-content: center;
    align-items: center;
    .circle {
      border: 1px solid #c7cbce;
      border-radius: 50%;
      padding: 8px 15px;
      cursor: pointer;
      font-weight: bold;
      background-color: #fff;
      color: #000;
      &:hover {
        border-color: #3490dc;
      }
    }
    .number {
      border: 1px solid #c7cbce;
      height: 35px;
      width: 50px;
      line-height: 35px;
      margin: 10px;
      text-align: center;
    }
  }
  .action {
    margin-top: 10px;
    text-align: right;
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, size, isEmpty, includes } from 'lodash'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'
import ScanTakerModal from './ScanTakerModal'

const STEP = {
  CHOOSE_ITEMS: 'choose-items',
  ISSUE_FORM: 'issue-form'
}

export default {
  components: {
    ScanTakerModal
  },

  props: {
    pols: { type: Array, default: () => [] }
  },

  mixins: [SelectBoxesMixin, RemoveErrorsMixin],

  data () {
    return {
      STEP,
      currentStep: STEP.CHOOSE_ITEMS,
      scanTakerModal: 'scan-taker-modal'
    }
  },

  computed: {
    noData () {
      return !size(this.data)
    },

    noSelectedData () {
      return isEmpty(this.selectedItems)
    },

    issuingData () {
      return chain(this.data)
        .filter(record => !!record.quantity)
        .value()
    },

    noIssuingData () {
      return isEmpty(this.issuingData)
    },

    numCols () {
      return this.currentStep === STEP.CHOOSE_ITEMS ? 6 : 7
    },

    isIssuingStep () {
      return this.currentStep === STEP.ISSUE_FORM
    }
  },

  watch: {
    pols () {
      chain(this.pols)
        .each(record => {
          const qty = parseInt(record.quantity_oh) || 0
          this.$set(record, 'visible', true)
          this.$set(record, 'quantity_oh', +record.quantity_oh)
          this.$set(record, 'quantity', 0)
        })
        .value()

      this.data = this.pols
    }
  },

  methods: {
    onClickIncrease (item) {
      const max = item.quantity_oh

      const number = item.quantity + 1
      this.$set(item, 'quantity', number <= max ? number : max)
    },

    onClickDecrease (item) {
      const number = item.quantity - 1
      this.$set(item, 'quantity', number < 1 ? 0 : number)
    },

    onClickGotoCard () {
      this.currentStep = STEP.ISSUE_FORM
      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', includes(this.selectedItems, item.id))
        })
        .value()
    },

    onClickCancel () {
      this.currentStep = STEP.CHOOSE_ITEMS

      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', true)
          this.$set(item, 'quantity', 0)
          this.$set(item, 'is_checked', false)
        })
        .value()
    },

    onClickCheckout () {
      if (this.noIssuingData) {
        return
      }

      const issuePolCallback = (pols) => {
        rf.getRequest('AdminRequest').issuePol({ pols })
          .then(res => {
            this.currentStep = STEP.CHOOSE_ITEMS
            this.showSuccess()
            this.$emit('done')
          })
          .catch(error => {
            this.processAndToastFirstError(error)
          })
      }

      const callback = () => {
        this.$modal.show(this.scanTakerModal, {
          type: Const.POL_ACTION.ISSUE,
          data: this.issuingData,
          callback: issuePolCallback
        })
      }

      this.confirmAction({ callback })
    }
  }
}
</script>
