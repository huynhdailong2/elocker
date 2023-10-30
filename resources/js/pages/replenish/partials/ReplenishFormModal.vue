<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title" v-if="spare">Replenish Form</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content" v-if="spare">
      <div class="row">
        <div class="col-sm-3">
          <label>Bin #:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="`${spare.cabinet_name} - ${ spare.locations.bin.row } - ${ spare.locations.bin.bin }`">
        </div>
        <div class="col-sm-3">
          <label>Item Name:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.locations.spares.name">
        </div>
        <div class="col-sm-3">
          <label>Item P/N:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.locations.spares.part_no">
        </div>
        <div class="col-sm-3">
          <label>Item Type:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.locations.spares.type | upperFirst">
        </div>
      </div>
      <div class="row">
        <div class="col-sm-3">
          <label>Quantity OH:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.bin_spare.quantity_oh || 0">
        </div>
        <div class="col-sm-3">
          <label>Quantity RL:</label>
          <input
            type="text"
            class="input"
            :class="{'error': errors.has('quantity')}"
            name="quantity"
            placeholder="Quantity RL"
            data-vv-as="quantity rl"
            v-model.trim="inputForm.quantity"
            v-validate="`numeric|max_value:${maxQuantity}`" >
          <span class="invalid-feedback" v-if="errors.has('quantity')">
            {{ errors.first('quantity') }}
          </span>
        </div>
        <div class="col-sm-3">
          <label>Min:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.bin_spare.min">
        </div>
        <div class="col-sm-3">
          <label>Max:</label>
          <input
            type="text"
            class="input"
            disabled
            :value="spare.bin_spare.max">
        </div>
      </div>

     <bin-configures
        :spare="spare"
        :data="inputForm.configures"
        ref="binConfigures"
        v-if="visibleBinConfigure" />

      <div class="row">
        <div class="col-sm-12 text-center">
          <button class="btn btn-primary" @click.stop="onClickSubmit">Submit</button>
        </div>
      </div>
    </div>

  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .row {
    margin-bottom: 20px;
    label {
      color: #fff;
    }
  }
}
</style>
<script>
import moment from 'moment'
import { chain, size, debounce } from 'lodash'
import Utils from 'common/Utils'
import Const from 'common/Const'
import BinConfigures from 'pages/maintenance/partial/BinConfigures'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'

export default {
  components: {
    BinConfigures
  },

  props: {
    name: {
      type: String,
      default: 'replenish-form-modal'
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      spare: null,
      inputForm: {
        quantity: null,
        configures: []
      }
    }
  },

  computed: {
    maxQuantity () {
      if (!this.spare) {
        return 0
      }

      const number = (this.spare.bin_spare.max || 0) - (this.spare.bin_spare.quantity_oh || 0)
      return number < 0 ? 0 : number
    },

    visibleBinConfigure () {
      return this.spare.configures.has_batch_no || this.spare.configures.has_serial_no
        || this.spare.configures.has_charge_time || this.spare.configures.has_calibration_due
        || this.spare.configures.has_expiry_date|| this.spare.configures.has_load_hydrostatic_test_due
    }
  },

  watch: {
    'inputForm.quantity': debounce(function () {
      this.initConfigures()

      const limit = parseInt(this.inputForm.quantity) || 0
      if (!limit) {
        return
      }
      this.inputForm.configures = chain(this.inputForm.configures).take(limit).value()
    }, 300)
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.spare = params?.spare

      this.inputForm = {
        ...this.inputForm,
        ...(this.spare.inputForm || {})
      }

      this.initConfigures()
    },

    close () {
      this.inputForm = {
        quantity: null,
        configures: []
      }
      this.$modal.hide(this.name)
    },

    initConfigures () {
      // if (!this.inputForm.quantity || this.inputForm.quantity > this.maxQuantity) {
      //   this.spare.configures = []
      //   return
      // }

      // const limit = parseInt(this.inputForm.quantity) || 0
      const limit = 1
      const currentSize = size(this.inputForm.configures)
      const needMore = (limit - currentSize) < 0 ? 0 : (limit - currentSize)

      const newData = Array(needMore).fill({
        batch_no: null,
        serial_no: null,
        has_charge_time: null,
        charge_time: null,
        has_calibration_due: null,
        calibration_due: null,
        has_expiry_date: null,
        expiry_date: null,
        has_load_hydrostatic_test_duee: null,
        load_hydrostatic_test_duee: null
      })

      this.inputForm.configures = chain(this.inputForm.configures || [])
        .concat(newData)
        .map((item, index) => {
          return {
            ...item,
            scope: `row-${index + 1}`,
            input_charge_time: Utils.stringTime2Object(item.charge_time),
            calibration_due: Utils.utcToClient(item.calibration_due),
            expiry_date: Utils.utcToClient(item.expiry_date),
            load_hydrostatic_test_due: Utils.utcToClient(item.load_hydrostatic_test_due)
          }
        })
        .value()
    },

    async onClickSubmit () {
      this.resetError()

      await this.$validator.validateAll()

      let isValidConfigures = true
      if (this.$refs.binConfigures) {
        isValidConfigures = await this.$refs.binConfigures.validateData()
      }

      if (this.errors.any() || !isValidConfigures) {
        return
      }

      const toUTc = (date) => {
        return date ? new moment(date).utc().format(Const.DATE_PATTERN) : null
      }

      chain(this.inputForm.configures)
        .each(item => {
          item.charge_time = Utils.objTime2String(item.input_charge_time),
          item.calibration_due = toUTc(item.calibration_due)
          item.expiry_date = toUTc(item.expiry_date)
          item.load_hydrostatic_test_due = toUTc(item.load_hydrostatic_test_due)
        })
        .value()

      this.$emit('done', this.inputForm)
      this.close()
    },
  }
}
</script>
