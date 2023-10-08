<template>
  <div class="vehicles">
    <div class="head">
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
            <td><div class="text">{{ props.item.vehicle_num }}</div></td>
            <td><div class="text">{{ props.item.vehicle_type_name }}</div></td>
            <td>
              <div class="text">{{ props.item.unit === Const.VEICHLE_UNITS.OTHERS.value ? props.item.unit_other : props.item.unit | uppercase }}</div>
            </td>
            <td><div class="text">{{ props.item.mileage_start }}</div></td>
            <td><div class="text">{{ props.item.mileage_end }}</div></td>
            <td><div class="text">{{ props.item.t_loan ? 'Yes' : 'No' }}</div></td>
            <td><div class="text">{{ props.item.unserviceable ? 'Yes' : 'No' }}</div></td>
            <td>
              <div class="text">{{ props.item.last_point_servicing | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
              <div class="text">{{ props.item.schedule_6_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
                <div class="text">{{ props.item.completion_date_6_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div></td>
            <td>
              <div class="text">{{ props.item.schedule_12_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
                <div class="text">{{ props.item.completion_date_12_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
              <div class="text">{{ props.item.schedule_18_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
                <div class="text">{{ props.item.completion_date_18_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div></td>
            <td>
              <div class="text">{{ props.item.schedule_24_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <div class="choose-date">
                  <datepicker
                    :clear-button="true"
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="props.item.formInput.completion_date_24_months"
                    :disabled-dates="{to: yesterday}"
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
          width: 100%;
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

  methods: {
    getVehicles(params) {
      params = {
        ...params,
        status: [Const.VEHICLE_STATUS.COMPLETING.value],
        search_key: this.inputSearch
      }
      return rf.getRequest('VehicleRequest').getVehicles(params)
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

    onClickCancel (record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${ index + 1 }.*`
      this.errors.clear(scope)
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

      record.formInput.completion_date_24_months = getDate(record.formInput.completion_date_24_months)

      this.submitRequest(record.formInput)
        .then(res => {
          this.showSuccess()
          this.$refs.datatable.refresh()
        })
        .catch(error => {
          this.processErrors(error, scope)
        })
    },

    submitRequest (data) {
      return rf.getRequest('VehicleRequest').revertVehicle(data)
    }
  }
}
</script>
