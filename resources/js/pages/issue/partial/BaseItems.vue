<script>
import rf from 'requestfactory'
import { chain, isEmpty, includes, head, toLower } from 'lodash'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'
import Utils from 'common/Utils'
import ScanTakerModal from './ScanTakerModal'

const STEP = {
  CHOOSE_ITEMS: 'choose-items',
  ISSUE_FORM: 'issue-form'
}

export default {
  components: {
    ScanTakerModal
  },

  mixins: [SelectBoxesMixin, RemoveErrorsMixin],

  data () {
    return {
      STEP,
      currentStep: STEP.CHOOSE_ITEMS,
      torqueAreas: [],
      scanTakerModal: 'scan-taker-modal',
    }
  },

  computed: {
    selectedSpares () {
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
        .every(item => !!item.spares.pivot.quantity)
        .value()
    },

    issueFormStep () {
      return this.currentStep === STEP.ISSUE_FORM
    }
    
  },

  mounted () {
    // this.$modal.show(this.scanTakerModal)
    this.getItemsForIssuing()
    this.getTorqueAreas()
  },

  methods: {
    getItemsForIssuing () {
      const params = {
        no_pagination: true,
        include_is_virtual: true,
      }
      rf.getRequest('AdminRequest').getItemsForIssuing(params)
        .then(res => {
          const data = res.data

          this.data = chain(data.spare_bins || [])
            .concat(data.spare_eucs || [])
            .map((item, index) => {
              let location = `${item.cluster_name} - ${item.shelf_name} - ${item.row} - ${item.bin}`
              if (item.type === Const.ITEM_TYPE.EUC.value && item.euc_box_order) {
                location = `EUC #${item.euc_box_order}`
              }
              return {
                ...item,
                id: index + 1,
                location,
                scope: `row-${index + 1}`,
                visible: true,
                url: item.url || '/images/icons/no-image.png',
                quantity: null,
                newQuantity: item.spares.pivot.quantity,
              }
            })
            .value()
            console.log("this.data",this.data)
        })
    },

    nextIssueFormStep () {
      this.currentStep = STEP.ISSUE_FORM
      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', includes(this.selectedItems, item.id))
        })
        .value()
    },

    onClickIncrease(item) {
      this.resetError()
      const max = item.spares.pivot.quantity_oh
      const number = item.newQuantity + 1
      this.$set(item, 'newQuantity', number <= max ? number : max)
    },

    onClickDecrease(item) {
      this.resetError()
      const number = item.newQuantity - 1
      this.$set(item, 'newQuantity', number < 1 ? 0 : number)
    },

    onCancel () {
      this.currentStep = STEP.CHOOSE_ITEMS

      // this.resetCheckedbox()
      chain(this.data)
        .each(item => {
          this.$set(item, 'visible', true)
          this.$set(item, 'spares.pivot.quantity', null)
          this.$set(item, 'is_checked', false)
        })
        .value()
    },

    async onCheckout () {
      this.resetError()
      await this.$validator.validateAll();

      if (this.noQuantity) {
        return
      }

      const spares = chain(this.selectedSpares)
        .map(item => {
          return {
            ...item,
            job_card_id: this.$attrs.jobCard.id,
            bin_id: item.bin_id,
            euc_box_id: item?.euc_box_id
          }
        })
        .value()

      await Utils.asyncForEach(spares, async (item, index) => {
        const scope = `${item.scope}.*`
        await this.$validator.validate(scope)
      })

      if (this.errors.any()) {
        return
      }

      const issueCardCallback = (spares) => {
        rf.getRequest('SpareRequest').issueCard({ spares })
          .then(res => {
            this.showSuccess('Successfully!')
          })
          .catch(error => {
            this.processAndToastFirstError(error)
          })
      }

      const _callback = () => {
        this.$modal.show(this.scanTakerModal, {
          callback: issueCardCallback,
          data: spares,
          jobCard: this.$attrs.jobCard,
          torqueAreas: this.torqueAreas
        })
      }

      this.confirmAction({ callback: _callback })
    },

    filter ({ text, type }) {
      chain(this.data)
        .each(item => {
          // no search
          if ((!text || isEmpty(text)) && type == '') {
            this.$set(item, 'visible', true)
            return
          }

          const visible = includes(toLower(item.material_no), toLower(text))
            || includes(toLower(item.part_no), toLower(text))
            || includes(toLower(item.name), toLower(text))
          const visibleType = type ? item.type == type : true;
          this.$set(item, 'visible', (visible && visibleType))
        })
        .value()
    },

    visibleTorqueArea(item) {
      console.log(item)
      switch (item.spares.type) {
        // case Const.ITEM_TYPE.DURABLE.value:
        case Const.ITEM_TYPE.TORQUE_WRENCH.value:
          return true
      }

      return false
    },

    getTorqueAreaRule (item) {
      switch (item.type) {
        case Const.ITEM_TYPE.CONSUMABLE.value:
          return ''
      }
      return 'required'
    },

    getTorqueAreas () {
      const params = {
        no_pagination: true
      }
      rf.getRequest('AdminRequest').getTorqueAreas(params)
        .then(res => {
          return this.torqueAreas = res.data || []
        })
    },

    getTorqueValue (record) {
      const result = chain(this.torqueAreas)
        .filter(item => item.id === record.torque_wrench_area_id)
        .head()
        .value()
      return result ? parseInt(result.torque_value) : null
    },

    getExpiryDate (record) {
      const item = head(record.configures || [])
      return item && item.has_expiry_date ? item.expiry_date : ''
    },

    getCalibrationDueDate (record) {
      const item = head(record.configures || [])
      return item && item.has_calibration_due ? item.calibration_due : ''
    },

    getLoadHydrostaticDueDate (record) {
      const item = head(record.configures || [])
      return item && item.has_load_hydrostatic_test_due ? item.load_hydrostatic_test_due : ''
    }

  }
}
</script>
