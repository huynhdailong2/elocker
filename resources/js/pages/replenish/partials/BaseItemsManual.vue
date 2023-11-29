<script>
import rf from 'requestfactory'
import { chain, isEmpty, includes, cloneDeep } from 'lodash'
import moment from 'moment'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'
import Utils from 'common/Utils'
import ReplenishFormModal from './ReplenishFormModal'
import ReplenishManualPrintModal from './ReplenishManualPrintModal'

const STEP = {
  CHOOSE_ITEMS: 'choose-items',
  REPLENISH_FORM: 'replenish-form'
}

export default {
  components: {
    ReplenishFormModal,
    ReplenishManualPrintModal
  },

  mixins: [SelectBoxesMixin, RemoveErrorsMixin],

  data () {
    return {
      STEP,
      currentStep: STEP.CHOOSE_ITEMS,
      selectedSpare: null,
      replenishPrintModal: 'ReplenishPrintModal'
    }
  },

  computed: {
    selectedSpares() {
      return chain(this.data)
        .filter(item => item.is_checked || includes(this.selectedItems, item.id))
        .value()
    },

    noData () {
      return isEmpty(this.data)
    },

    noSelectedData () {
      return isEmpty(this.selectedItems)
    },

    noQuantity () {
      if (isEmpty(this.selectedSpares)) {
        return true
      }

      return !chain(this.selectedSpares)
        .every(item => !!item.quantity)
        .value()
    },

    replenishForm () {
      return this.currentStep === STEP.REPLENISH_FORM
    }
  },

  mounted () {
    this.getSparesAssignedBin()
  },

  methods: {
    getSparesAssignedBin () {
      const types = [
        Const.ITEM_TYPE.CONSUMABLE.value,
        Const.ITEM_TYPE.DURABLE.value,
        Const.ITEM_TYPE.PERISHABLE.value,
        Const.ITEM_TYPE.AFES.value,
        Const.ITEM_TYPE.TORQUE_WRENCH.value,
        Const.ITEM_TYPE.OTHERS.value
      ]

      const params = {
        no_pagination: true,
        can_replenishment: true,
        types
      }
      rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        .then(res => {
          this.data = chain(res.data || [])
            // .filter(item => !!item.quantity)
            .map((item, index) => {
              return {
                ...item,
                scope: `row-${index + 1}`,
                visible: true,
                url: item.url || '/images/icons/no-image.png',
                quantity_rl: null
              }
            })
            .value()
        })
    },

    nextReplenishForm () {
      this.currentStep = STEP.REPLENISH_FORM
      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', includes(this.selectedItems, item.id))
        })
        .value()
    },

    cancelReplenishForm () {
      this.currentStep = STEP.CHOOSE_ITEMS
      // this.resetCheckedbox()
      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', true)
          this.$set(item, 'is_checked', false)
        })
        .value()
    },

    async onCheckout() {
      if (this.noSelectedData) {
        return
      }
      const spares = chain(this.selectedSpares)
        .map(item => {
          const inputForm = item.inputForm
          return { ...inputForm, 
            bin_id: item.bin_id, 
            // spare_id: item.bin_spare.spare_id,
            spare_id: item.spare_id,
            taking_transaction_id: item.id,
            quantity: +inputForm?.quantity || 0

          }
        })
        .value()
      const callback = async () => {
        try {
          await rf.getRequest('SpareRequest').replenishManual({ spares })

          this.showSuccess('Successfully!')
          this.$modal.show(this.replenishPrintModal, { data: this.selectedSpares })
        } catch (error) {
          this.processAndToastFirstError(error)
        }
      }

      this.confirmAction({ callback })
    },

    filter ({ text }) {
      chain(this.data)
        .each(item => {
          // no search
          if (!text || isEmpty(text)) {
            this.$set(item, 'visible', true)
            return
          }

          const visible = includes(item.material_no, text) || includes(item.part_no, text)
          this.$set(item, 'visible', visible)
        })
        .value()
    },

    onClickFillReplenish (item) {
      this.selectedSpare = item
      this.$modal.show('replenish-form-modal', { spare: item })
    },

    handleReplenishFormFinished (data) {
      this.$set(this.selectedSpare, 'inputForm', data)
    }
  }
}
</script>
