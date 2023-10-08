<template>
  <div class="vehicles">
    <div class="head">
      <button class="btn-primary" @click.stop="onClickAddNew">Add Vehicle</button>
      <div class="search-form">
        <input
          type="text"
          placeholder="Search ..."
          class="input"
          v-model="inputSearch">
      </div>
    </div>
    <div class="table-content">
      <data-table2 :getData="getVehicles"
          :limit="10"
          :column="18"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">S/N</th>
          <!-- <th class="text-center">Name</th> -->
          <th class="text-center w_110px" data-sort-field="vehicles.vehicle_num">Veh no.</th>
          <th class="text-center w_110px" data-sort-field="vehicle_types.name">Variant</th>
          <!-- <th class="text-center w_110px" data-sort-field="vehicles.variant">Variant</th> -->
          <th class="text-center w_110px" data-sort-field="vehicles.unit">Unit</th>
          <th class="text-center w_120px" data-sort-field="vehicles.mileage_start">Mileage Start</th>
          <th class="text-center w_120px" data-sort-field="vehicles.mileage_end">Mileage End</th>
          <th class="text-center w_110px" data-sort-field="vehicles.t_loan">T-loan</th>
          <th class="text-center mw_170px" data-sort-field="vehicles.unserviceable">Unserviceable</th>
          <th class="text-center mw_140px" data-sort-field="vehicles.last_point_servicing">Last O Point Servicing</th>
          <th class="text-center w_110px" data-sort-field="vehicles.schedule_6_months">6 mth Plan</th>
          <th class="text-center mw_140px" data-sort-field="vehicles.completion_date_6_months">Completion Date</th>
          <th class="text-center w_110px" data-sort-field="vehicles.schedule_12_months">12 mth Plan</th>
          <th class="text-center mw_140px" data-sort-field="vehicles.completion_date_12_months">Completion Date</th>
          <th class="text-center w_110px" data-sort-field="vehicles.schedule_18_months">18 mth Plan</th>
          <th class="text-center mw_140px" data-sort-field="vehicles.completion_date_18_months">Completion Date</th>
          <th class="text-center w_110px" data-sort-field="vehicles.schedule_24_months">24 mth Plan</th>
          <th class="text-center mw_140px" data-sort-field="vehicles.completion_date_24_months">Completion Date</th>
          <th class="w_110px">Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td><div class="text">{{ props.realIndex }}</div></td>
            <!-- <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`${props.item.scope}.name`)}"
                  name="name"
                  :data-vv-scope="`${props.item.scope}`"
                  placeholder="Name"
                  v-model.trim="props.item.formInput.name"
                  v-validate="'required'" >
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.name`)">
                  {{ errors.first(`${props.item.scope}.name`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.name }}</div>
              </template>
            </td> -->
            <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`${props.item.scope}.vehicle_num`)}"
                  name="vehicle_num"
                  data-vv-as="vehicle no"
                  placeholder="Vehicle No"
                  :data-vv-scope="`${props.item.scope}`"
                  v-model.trim="props.item.formInput.vehicle_num"
                  v-validate="'required'" >
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.vehicle_num`)">
                  {{ errors.first(`${props.item.scope}.vehicle_num`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.vehicle_num }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.vehicle_type_id`)}"
                    v-model="props.item.formInput.vehicle_type_id"
                    name="vehicle_type_id"
                    data-vv-as="vehicle type"
                    v-validate="'required'" >
                  <option :value="item.id" v-for="(item, index) in vehicleTypes" :key="index">{{ item.name }}</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.vehicle_type_id`)">
                  {{ errors.first(`${props.item.scope}.vehicle_type_id`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.vehicle_type_name }}</div>
              </template>
            </td>
            <!-- <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.variant`)}"
                    v-model="props.item.formInput.variant"
                    name="variant"
                    data-vv-as="variant"
                    v-validate="'required'" >
                  <option :value="item.value" v-for="(item, index) in variants" :key="index">{{ item.name }}</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.variant`)">
                  {{ errors.first(`${props.item.scope}.variant`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.variant | uppercase }}</div>
              </template>
            </td> -->
            <td>
              <template v-if="props.item.editable">
                <div class="d-flex">
                  <select
                      :data-vv-scope="`${props.item.scope}`"
                      :class="{
                        'error': errors.has(`${props.item.scope}.unit`),
                        'fit-content': props.item.formInput.unit === Const.VEICHLE_UNITS.OTHERS.value
                      }"
                      v-model="props.item.formInput.unit"
                      name="unit"
                      data-vv-as="unit"
                      v-validate="'required'" >
                    <option :value="item.value" v-for="(item, index) in units" :key="index">{{ item.name }}</option>
                  </select>
                  <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.variant`)">
                    {{ errors.first(`${props.item.scope}.variant`) }}
                  </span>
                  <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`${props.item.scope}.unit_other`)}"
                    name="unit_other"
                    data-vv-as="unit"
                    placeholder="Unit"
                    :data-vv-scope="`${props.item.scope}`"
                    v-model.trim="props.item.formInput.unit_other"
                    v-validate="'required'"
                    v-if="props.item.formInput.unit === Const.VEICHLE_UNITS.OTHERS.value" >
                  <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.unit_other`)">
                    {{ errors.first(`${props.item.scope}.unit_other`) }}
                  </span>
                </div>
              </template>
              <template v-else>
                <div class="text">{{ props.item.unit === Const.VEICHLE_UNITS.OTHERS.value ? props.item.unit_other : props.item.unit | uppercase }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <!-- <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.mileage_start"
                    :disabled-dates="{to: yesterday}"
                    name="mileage_start"
                    data-vv-as="mileage start"
                    v-validate="'required'"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.mileage_start`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.mileage_start`)">
                  {{ errors.first(`${props.item.scope}.mileage_start`) }}
                </span> -->
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`${props.item.scope}.mileage_start`)}"
                    name="mileage_start"
                    data-vv-as="mileage start"
                    placeholder="Mileage Start"
                    :data-vv-scope="`${props.item.scope}`"
                    v-model.trim="props.item.formInput.mileage_start"
                    v-validate="'required'">
                  <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.mileage_start`)">
                    {{ errors.first(`${props.item.scope}.mileage_start`) }}
                  </span>
              </template>
              <template v-else>
                <!-- <div class="text">{{ props.item.mileage_start | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div> -->
                <div class="text">{{ props.item.mileage_start }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <!-- <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.mileage_end"
                    :disabled-dates="{to: yesterday}"
                    name="mileage_end"
                    data-vv-as="mileage end"
                    v-validate="'required'"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.mileage_end`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.mileage_end`)">
                  {{ errors.first(`${props.item.scope}.mileage_end`) }}
                </span> -->
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`${props.item.scope}.mileage_end`)}"
                    name="mileage_end"
                    data-vv-as="mileage end"
                    placeholder="Mileage end"
                    :data-vv-scope="`${props.item.scope}`"
                    v-model.trim="props.item.formInput.mileage_end"
                    v-validate="'required'" >
                  <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.mileage_end`)">
                    {{ errors.first(`${props.item.scope}.mileage_end`) }}
                  </span>
              </template>
              <template v-else>
                <!-- <div class="text">{{ props.item.mileage_end | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div> -->
                <div class="text">{{ props.item.mileage_end }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.t_loan`)}"
                    v-model="props.item.formInput.t_loan"
                    name="t_loan"
                    data-vv-as="t-loan"
                    v-validate="'required'" >
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.t_loan`)">
                  {{ errors.first(`${props.item.scope}.t_loan`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.t_loan ? 'Yes' : 'No' }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.unserviceable`)}"
                    v-model="props.item.formInput.unserviceable"
                    name="unserviceable"
                    data-vv-as="unserviceable"
                    v-validate="'required'" >
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.unserviceable`)">
                  {{ errors.first(`${props.item.scope}.unserviceable`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.unserviceable ? 'Yes' : 'No' }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.last_point_servicing"
                    name="last_point_servicing"
                    data-vv-as="last point servicing"
                    v-validate="''"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.last_point_servicing`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.last_point_servicing`)">
                  {{ errors.first(`${props.item.scope}.last_point_servicing`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.last_point_servicing | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ calculateDatePlan(props.item.formInput.last_point_servicing, 6) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.completion_date_6_months"
                    name="completion_date_6_months"
                    data-vv-as="completion date"
                    v-validate="''"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.completion_date_6_months`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.completion_date_6_months`)">
                  {{ errors.first(`${props.item.scope}.completion_date_6_months`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.completion_date_6_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ calculateDatePlan(props.item.formInput.last_point_servicing, 12) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.completion_date_12_months"
                    name="completion_date_12_months"
                    data-vv-as="completion date"
                    v-validate="''"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.completion_date_12_months`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.completion_date_12_months`)">
                  {{ errors.first(`${props.item.scope}.completion_date_12_months`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.completion_date_12_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ calculateDatePlan(props.item.formInput.last_point_servicing, 18) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.completion_date_18_months"
                    name="completion_date_18_months"
                    data-vv-as="completion date"
                    v-validate="''"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.completion_date_18_months`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.completion_date_18_months`)">
                  {{ errors.first(`${props.item.scope}.completion_date_18_months`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.completion_date_18_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ calculateDatePlan(props.item.formInput.last_point_servicing, 24) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.completion_date_24_months"
                    name="completion_date_24_months"
                    data-vv-as="completion date"
                    v-validate="''"
                    :data-vv-scope="`${props.item.scope}`"
                    :class="{'error': errors.has(`${props.item.scope}.completion_date_24_months`)}" />
                </div>
                <span class="invalid-feedback" v-if="errors.has(`${props.item.scope}.completion_date_24_months`)">
                  {{ errors.first(`${props.item.scope}.completion_date_24_months`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.completion_date_24_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </template>
            </td>
            <td class="action">
              <template v-if="props.item.editable">
                <img src="/images/icons/icon-cancel.svg" width="22px"
                  @click.stop="onClickCancel(props.item, props.index)">
                <img src="/images/icons/icon-save.svg" width="22px"
                  @click.stop="onClickSave(props.item, props.index)">
              </template>
              <template v-else>
                <img src="/images/icons/icon-edit.svg" width="22px" @click.stop="props.item.editable = true">
                <img src="/images/icons/icon-trash.svg" width="22px" @click.stop="onClickDelete(props.item, props.index)">
              </template>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>
  </div>
</template>
<style lang="scss" scoped>
$heightCell: 40px;
$widthCell: 150px;

.vehicles {
  width: 100%;
  .head {
    display: flex;
    justify-content: space-between;
    margin: 20px 0 10px 0;
    .search-form {
      width: 300px;
    }
  }
  .table-content {
    ::v-deep .box_table {
      overflow: auto;
      th {
        vertical-align: middle;
      }
      td {
        padding: 0px;
        input {
          height: $heightCell;
          width: $widthCell;
        }
        select {
          height: $heightCell;
          min-width: $widthCell;
          width: 100%;
          &.fit-content {
            min-width: 80px;
            margin-right: 5px;
          }
        }
        &.action {
          line-height: 38px;
          img {
            margin: 0 0 0 10px;
            cursor: pointer;
            &:last-child {
              margin-right: 10px;
            }
          }
        }
        .text {
          vertical-align: middle;
          text-align: center;
          line-height: 38px;
        }
        .vdp-datepicker {
          &__clear-button {
            position: absolute;
            top: 7px;
            right: 10px;
          }
        }
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import moment from 'moment'
import Const from 'common/Const'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { chain, cloneDeep, concat, debounce } from 'lodash'
import Datepicker from 'vuejs-datepicker'

export default {
  components: {
    Datepicker
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      inputSearch: null,
      data: [],
      vehicleTypes: [],
      Const
    }
  },

  computed: {
    yesterday () {
      return moment().subtract(1, 'days').toDate()
    },

    variants () {
      return Object.values(Const.VEICHLE_VARIANT_TYPE)
    },

    units () {
      return Object.values(Const.VEICHLE_UNITS)
    }
  },

  watch: {
    inputSearch: debounce(function () {
      this.$nextTick(() => {
        this.$refs.datatable.refresh()
      })
    }, 300)
  },

  mounted () {
    this.getVehicleTypes()
  },

  methods: {
    getVehicles(params) {
      params = {
        ...params,
        search_key: this.inputSearch
      }
      return rf.getRequest('VehicleRequest').getVehicles(params)
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

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows

      chain(this.data)
        .each((row, index) => {
          this.$set(row, 'editable', false)
          this.$set(row, 'scope', `row-${index + 1}`)
          this.$set(row, 'formInput', cloneDeep(row))
        })
        .value()
    },

    existsUnit(value) {
      return !chain(this.units)
        .filter(item => item.value === value)
        .isEmpty()
        .value()
    },

    onClickAddNew () {
      const size = this.data.length

      const newItem = {
        editable: true,
        scope: `row-${size + 1}`,
        name: null,
        vehicle_num: null,
        vehicle_type_id: null,
        variant: null,
        unit: null,
        unit_other: null,
        mileage_start: null,
        mileage_end: null,
        t_loan: null,
        unserviceable: null,
        last_point_servicing: null,
        schedule_6_months: null,
        completion_date_6_months: null,
        schedule_12_months: null,
        completion_date_12_months: null,
        schedule_18_months: null,
        completion_date_18_months: null,
        schedule_24_months: null,
        completion_date_24_months: null,
      }

      const newRecord = { ...newItem, formInput: cloneDeep(newItem) }
      this.data.unshift(newRecord)
    },

    onClickCancel (record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${ index + 1 }.*`
      this.errors.clear(scope)
    },

    onClickDelete (record, index) {
      if (!record.id) {
        this.$refs.datatable.refresh()
      }

      const callback = () => {
        rf.getRequest('VehicleRequest').deleteVehicle({ id: record.id })
          .then(res => {
            this.showSuccess('Successfully!');
            this.$refs.datatable.refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
      }

      this.confirmAction({ callback })
    },

    async onClickSave (record, index) {
      const scope = record.scope
      this.errors.clear(`${scope}.*`)

      await this.$validator.validate(`${scope}.*`)

      if (this.errors.any()) {
        return
      }

      const getDate = (value) => {
        return value ? moment(value).format(Const.DATE_PATTERN) : null
      }

      const calculateDate = (value, number) => {
        return value ? moment(value).add(number, 'month').format(Const.DATE_PATTERN) : null
      }

      // record.formInput.mileage_start = getDate(record.formInput.mileage_start)
      // record.formInput.mileage_end = getDate(record.formInput.mileage_end)
      record.formInput.last_point_servicing = getDate(record.formInput.last_point_servicing)
      record.formInput.schedule_6_months = getDate(calculateDate(record.formInput.last_point_servicing, 6))
      record.formInput.schedule_12_months = getDate(calculateDate(record.formInput.last_point_servicing, 12))
      record.formInput.schedule_18_months = getDate(calculateDate(record.formInput.last_point_servicing, 18))
      record.formInput.schedule_24_months = getDate(calculateDate(record.formInput.last_point_servicing, 24))
      record.formInput.completion_date_6_months = getDate(record.formInput.completion_date_6_months)
      record.formInput.completion_date_12_months = getDate(record.formInput.completion_date_12_months)
      record.formInput.completion_date_18_months = getDate(record.formInput.completion_date_18_months)
      record.formInput.completion_date_24_months = getDate(record.formInput.completion_date_24_months)

      this.submitRequest(record.formInput)
        .then(res => {
          this.showSuccess()

          this.$set(record, 'editable', false)

          this.$set(record, 'name', res.data.name)
          this.$set(record, 'vehicle_num', res.data.vehicle_num)
          this.$set(record, 'vehicle_type_name', res.data.vehicle_type_name)
          this.$set(record, 'vehicle_type_id', res.data.vehicle_type_id)

          this.$set(record, 'variant', res.data.variant)
          this.$set(record, 'unit', res.data.unit)
          this.$set(record, 'unit_other', res.data.unit_other)
          this.$set(record, 'mileage_start', res.data.mileage_start)
          this.$set(record, 'mileage_end', res.data.mileage_end)
          this.$set(record, 't_loan', res.data.t_loan)
          this.$set(record, 'unserviceable', res.data.unserviceable)
          this.$set(record, 'last_point_servicing', res.data.last_point_servicing)
          this.$set(record, 'schedule_6_months', res.data.schedule_6_months)
          this.$set(record, 'schedule_12_months', res.data.schedule_12_months)
          this.$set(record, 'schedule_18_months', res.data.schedule_18_months)
          this.$set(record, 'schedule_24_months', res.data.schedule_24_months)
          this.$set(record, 'completion_date_6_months', res.data.completion_date_6_months)
          this.$set(record, 'completion_date_12_months', res.data.completion_date_12_months)
          this.$set(record, 'completion_date_18_months', res.data.completion_date_18_months)
          this.$set(record, 'completion_date_24_months', res.data.completion_date_24_months)

          this.$set(record, 'formInput', cloneDeep(record))
        })
        .catch(error => {
          this.processErrors(error, scope)
        })
    },

    submitRequest (data) {
      if (data.id) {
        return rf.getRequest('VehicleRequest').updateVehicle(data)
      }
      return rf.getRequest('VehicleRequest').createVehicle(data)
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
