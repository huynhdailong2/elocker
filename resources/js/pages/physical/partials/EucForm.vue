<template>
  <div class="block euc-form">
    <div class="head">
        <div class="d-inline-block pointer mr-2 ic-back" @click.stop="onClickBack">
          <img src="/images/icons/icon-back2.svg" class="mr-2" width="25">
        </div>

        <div class="text">
          <span v-if="euc">Edit EUC Box</span>
          <span v-else>Create New EUC</span>
        </div>
      </div>
    <div class="container">
      <div class="row">
        <div class="col-sm-4">
          <label>EUC Box:</label>
          <input
            type="text"
            class="input"
            :class="{'error': errors.has('order')}"
            name="order"
            data-vv-as="euc box"
            placeholder="EUC Box"
            v-model.trim="inputForm.order"
            v-validate="'required|numeric|min_value:1'" >
          <span class="invalid-feedback" v-if="errors.has('order')">
            {{ errors.first('order') }}
          </span>
        </div>

        <div class="col-sm-4">
          <label>Vehicle Type:</label>
          <select
              class="input veh-type"
              :class="{'error': errors.has('vehicle_type_id')}"
              v-model="inputForm.vehicle_type_id"
              name="vehicle_type_id"
              data-vv-as="vehicle type"
              v-validate="'required'" >
            <option :value="item.id" v-for="(item, index) in vehicleTypes" :key="index">{{ item.name }}</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has('vehicle_type_id')">
            {{ errors.first('vehicle_type_id') }}
          </span>
        </div>

        <div class="col-sm-4">
          <label>Platform:</label>
          <input
            type="text"
            class="input"
            :class="{'error': errors.has('platform')}"
            name="platform"
            placeholder="Platform"
            v-model.trim="inputForm.platform"
            v-validate="'required'" >
          <span class="invalid-feedback" v-if="errors.has('platform')">
            {{ errors.first('platform') }}
          </span>
        </div>
      </div>

      <euc-items :data="inputForm.spares" />

      <div>
        <span class="invalid-feedback" v-if="errors.has('spares')">
          {{ errors.first('spares') }}
        </span>
      </div>

      <div class="text-right">
        <button class="btn btn-primary" @click.stop="onSubmit">Submit</button>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.euc-form {
  .head {
    position: relative;
    margin-top: 10px;
    margin-bottom: 30px;
    .ic-back {
      position: absolute;
      left: 0;
    }
    .text {
      text-align: center;
      font-size: 16px;
      font-weight: bold;
    }
  }
  .container {
    .veh-type {
      padding: 14px 15px;
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import { isEmpty, map } from 'lodash'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'
import EucItems from './EucItems'
import moment from 'moment'
import Utils from 'common/Utils'

export default {
  components: {
    EucItems
  },

  props: {
    euc: {
      type: Object,
      default: () => {}
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      inputForm: {
        order: null,
        vehicle_type_id: null,
        platform: null,
        spares: []
      },
      vehicleTypes: []
    }
  },

  watch: {
    'inputForm.spares': {
      deep: true,
      handler (newList) {
        if (!isEmpty(newList)) {
          this.errors.remove('spares')
        }
      }
    }
  },

  mounted () {
    this.inputForm = {
      ...this.inputForm,
      ...this.euc
    }

    this.getVehicleTypes()
  },

  methods: {
    onClickBack () {
      this.$emit('back')
    },

    getVehicleTypes() {
      const params = {
        no_pagination: true
      }
      rf.getRequest('VehicleRequest').getVehicleTypes(params)
        .then(res => {
          this.vehicleTypes = res.data || []
        })
    },

    async onSubmit () {
      this.resetError()

      this.validateManual()
      await this.$validator.validateAll()

      if (this.errors.any()) {
        return
      }

      const toUTc = (date) => {
        return date ? new moment(date).utc().format(Const.DATE_PATTERN) : null
      }

      this.inputForm.spares = map(this.inputForm.spares, item => {
        return {
          spare_id: item.spare_id,
          quantity_oh: item.quantity_oh,
          batch_no: item.batch_no,
          calibration_due: toUTc(item.calibration_due),
          charge_time: Utils.objTime2String(item.charge_time),
          expiry_date: toUTc(item.expiry_date),
          hydrostatic_test_due: toUTc(item.hydrostatic_test_due),
          serial_no: item.serial_no
        }
      })

      this.submitRequest(this.inputForm)
        .then(res => {
          this.showSuccess('Successfully!');
          this.$emit('done')
        })
        .catch(error => {
          this.processErrors(error)
        })
    },

    validateManual () {
      if (isEmpty(this.inputForm.spares)) {
        this.errors.add({field: 'spare_ids', msg: 'The spare field is required'})
      }
    },

    submitRequest (data) {
      if (data.id) {
        return rf.getRequest('AdminRequest').updateEucList(data)
      }
      return rf.getRequest('AdminRequest').createEucList(data)
    }
  }
}
</script>
