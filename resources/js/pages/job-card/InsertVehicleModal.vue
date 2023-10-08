<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Add Vehicle</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="row">
        <div class="col-sm-4">
          <label>Vehicle No</label>
          <input
            type="text"
            class="input"
            name="vehicle_num"
            data-vv-as="vehicle no"
            placeholder="Vehicle No"
            v-model.trim="vehicle.vehicle_num"
            v-validate="'required'" >
            <span class="invalid-feedback" v-if="errors.has(`vehicle_num`)">
              {{ errors.first(`vehicle_num`) }}
            </span>
        </div>
        <div class="col-sm-4">
          <label>Variant</label>
          <select
            class="input"
            v-model="vehicle.vehicle_type_id"
            name="vehicle_type_id"
            data-vv-as="vehicle type"
            v-validate="'required'" >
            <option :value="item.id" v-for="(item, index) in vehicleTypes" :key="index">{{ item.name }}</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has(`vehicle_type_id`)">
            {{ errors.first(`vehicle_type_id`) }}
          </span>
        </div>
        <div class="col-sm-4">
          <label>Unit</label>
          <div class="d-flex">
            <select
              class="input"
              :class="{
                'fit-content': vehicle.unit === Const.VEICHLE_UNITS.OTHERS.value
              }"
              v-model="vehicle.unit"
              name="unit"
              data-vv-as="unit"
              v-validate="'required'" >
              <option :value="item.value" v-for="(item, index) in units" :key="index">{{ item.name }}</option>
            </select>

            <input
              type="text"
              class="input"
              name="unit_other"
              data-vv-as="unit"
              placeholder="Unit"
              v-model.trim="vehicle.unit_other"
              v-validate="'required'"
              v-if="vehicle.unit === Const.VEICHLE_UNITS.OTHERS.value" >
            <span class="invalid-feedback" v-if="errors.has(`unit_other`)">
              {{ errors.first(`unit_other`) }}
            </span>
          </div>
          <span class="invalid-feedback" v-if="errors.has(`unit`)">
            {{ errors.first(`unit`) }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4">
          <label>Mileage Start</label>
          <input
            type="text"
            class="input"
            name="mileage_start"
            data-vv-as="mileage start"
            placeholder="Mileage Start"
            v-model.trim="vehicle.mileage_start"
            v-validate="'required'">
          <span class="invalid-feedback" v-if="errors.has(`mileage_start`)">
            {{ errors.first(`mileage_start`) }}
          </span>
        </div>
        <div class="col-sm-4">
          <label>Mileage Start</label>
          <input
            type="text"
            class="input"
            name="mileage_end"
            data-vv-as="mileage end"
            placeholder="Mileage end"
            v-model.trim="vehicle.mileage_end"
            v-validate="'required'" >
          <span class="invalid-feedback" v-if="errors.has(`mileage_end`)">
            {{ errors.first(`mileage_end`) }}
          </span>
        </div>
        <div class="col-sm-4">
          <label>T-loan</label>
          <select
            class="input"
            v-model="vehicle.t_loan"
            name="t_loan"
            data-vv-as="t-loan"
            v-validate="'required'" >
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has(`t_loan`)">
            {{ errors.first(`t_loan`) }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4">
          <label>Unserviceable</label>
          <select
            class="input"
            v-model="vehicle.unserviceable"
            name="unserviceable"
            data-vv-as="unserviceable"
            v-validate="'required'" >
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has(`unserviceable`)">
            {{ errors.first(`unserviceable`) }}
          </span>
        </div>
        <div class="col-sm-4">
          <label>Last O Point Servicing</label>
          <div class="choose-date">
            <datepicker
              :clear-button="true"
              format="dd/MM/yyyy"
              input-class="date-selector input"
              v-model="vehicle.last_point_servicing"
              name="last_point_servicing"
              data-vv-as="last point servicing"
              v-validate="''"
              :class="{'error': errors.has(`last_point_servicing`)}" />
          </div>
          <span class="invalid-feedback" v-if="errors.has(`last_point_servicing`)">
            {{ errors.first(`last_point_servicing`) }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3">
          <label>6 mth Plan</label>
          <div class="text">{{ calculateDatePlan(vehicle.last_point_servicing, 6) }}</div>
        </div>
        <div class="col-sm-3">
          <label>Completion Date</label>
          <div class="choose-date">
            <datepicker
              :clear-button="true"
              format="dd/MM/yyyy"
              input-class="input date-selector"
              v-model="vehicle.completion_date_6_months"
              name="completion_date_6_months"
              data-vv-as="completion date"
              v-validate="''"
              :class="{'error': errors.has(`completion_date_6_months`)}" />
          </div>
          <span class="invalid-feedback" v-if="errors.has(`completion_date_6_months`)">
            {{ errors.first(`completion_date_6_months`) }}
          </span>
        </div>
        <div class="col-sm-3">
          <label>12 mth Plan</label>
          <div class="text">{{ calculateDatePlan(vehicle.last_point_servicing, 12) }}</div>
        </div>
        <div class="col-sm-3">
          <label>Completion Date</label>
          <div class="choose-date">
            <datepicker
              :clear-button="true"
              format="dd/MM/yyyy"
              input-class="input date-selector"
              v-model="vehicle.completion_date_12_months"
              name="completion_date_12_months"
              data-vv-as="completion date"
              v-validate="''"
              :class="{'error': errors.has(`completion_date_12_months`)}" />
          </div>
          <span class="invalid-feedback" v-if="errors.has(`completion_date_12_months`)">
            {{ errors.first(`completion_date_12_months`) }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3">
          <label>18 mth Plan</label>
          <div class="text">{{ calculateDatePlan(vehicle.last_point_servicing, 18) }}</div>
        </div>
        <div class="col-sm-3">
          <label>Completion Date</label>
          <div class="choose-date">
            <datepicker
              :clear-button="true"
              format="dd/MM/yyyy"
              input-class="input date-selector"
              v-model="vehicle.completion_date_18_months"
              name="completion_date_18_months"
              data-vv-as="completion date"
              v-validate="''"
              :class="{'error': errors.has(`completion_date_18_months`)}" />
          </div>
          <span class="invalid-feedback" v-if="errors.has(`completion_date_18_months`)">
            {{ errors.first(`completion_date_18_months`) }}
          </span>
        </div>
        <div class="col-sm-3">
          <label>24 mth Plan</label>
          <div class="text">{{ calculateDatePlan(vehicle.last_point_servicing, 24) }}</div>
        </div>
        <div class="col-sm-3">
          <label>Completion Date</label>
          <div class="choose-date">
            <datepicker
              :clear-button="true"
              format="dd/MM/yyyy"
              input-class="input date-selector"
              v-model="vehicle.completion_date_24_months"
              name="completion_date_24_months"
              data-vv-as="completion date"
              v-validate="''"
              :class="{'error': errors.has(`completion_date_24_months`)}" />
          </div>
          <span class="invalid-feedback" v-if="errors.has(`completion_date_24_months`)">
            {{ errors.first(`completion_date_24_months`) }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 text-center">
          <button class="btn btn-second" @click.stop="close">Discard</button>
          <button class="btn btn-primary" @click.stop="onClickSubmit">Add Vehicle</button>
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
  .m-auto {
    margin: auto;
  }
  ::v-deep .choose-date {
    .vdp-datepicker {
      &__clear-button {
        position: absolute;
        top: 7px;
        right: 10px;
      }
      &__calendar {
        bottom: 0 !important;
      }
    }
  }
}
</style>

<script>
import rf from 'requestfactory';
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'
import Datepicker from 'vuejs-datepicker'
import moment from "moment";

export default {
  props: {
    name: {
      type: String,
      default: 'insert-vehicle-modal'
    }
  },

  components: {
    Datepicker
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      vehicle_num: null,
      vehicle: {
        vehicle_num: null,
        vehicle_type_id: null,
        unit: null,
        unit_other: null,
        mileage_start: null,
        mileage_end: null,
        t_loan: null,
        unserviceable: null,
        last_point_servicing: null,
        completion_date_6_months: null,
        completion_date_12_months: null,
        completion_date_18_months: null,
        completion_date_24_months: null,
      },
      vehicleTypes: [],
      Const,
    }
  },

  mounted () {
    this.getVehicleTypes()
  },

  computed: {
    units () {
      return Object.values(Const.VEICHLE_UNITS)
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.vehicle_num = params?.vehicle_num
      this.vehicle['vehicle_num'] = params?.vehicle_num
    },

    close () {
      this.spare = null;
      this.$modal.hide(this.name)
    },

    async onClickSubmit () {
      this.resetError();

      await this.$validator.validateAll();
      if (this.errors.any()) {
        return;
      }

      const getDate = (value) => {
        return value ? moment(value).format(Const.DATE_PATTERN) : null
      }

      const calculateDate = (value, number) => {
        return value ? moment(value).add(number, 'month').format(Const.DATE_PATTERN) : null
      }

      this.vehicle.last_point_servicing = getDate(this.vehicle.last_point_servicing)
      this.vehicle.schedule_6_months = getDate(calculateDate(this.vehicle.last_point_servicing, 6))
      this.vehicle.schedule_12_months = getDate(calculateDate(this.vehicle.last_point_servicing, 12))
      this.vehicle.schedule_18_months = getDate(calculateDate(this.vehicle.last_point_servicing, 18))
      this.vehicle.schedule_24_months = getDate(calculateDate(this.vehicle.last_point_servicing, 24))
      this.vehicle.completion_date_6_months = getDate(this.vehicle.completion_date_6_months)
      this.vehicle.completion_date_12_months = getDate(this.vehicle.completion_date_12_months)
      this.vehicle.completion_date_18_months = getDate(this.vehicle.completion_date_18_months)
      this.vehicle.completion_date_24_months = getDate(this.vehicle.completion_date_24_months)

      return rf.getRequest('VehicleRequest').createVehicle(this.vehicle)
        .then(res => {
          this.showSuccess('Successful!')
          this.$emit('done', res.data)
          this.close()
        })
        .catch(error => {
          this.processAndToastFirstError(error)
        })
    },

    copyCurrentValue(column) {
      this.vehicle[column] = this.vehicle.weight
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

    calculateDatePlan (value, number) {
      if (!value) {
        return null
      }
      return moment(value).add(number, 'month').format(Const.CLIENT_DATE_PATTERN)
    }
  }
}
</script>
