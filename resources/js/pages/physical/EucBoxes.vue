<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">EUC Management</div>
    </div>

    <div class="euc-list block" v-if="currentStep === STEP.ITEMS_LIST">
      <div class="action">
        <button class="btn-primary" @click.stop="onClickAdd">Add EUC</button>
      </div>

      <div class="table-content">
        <data-table2 :getData="getEucLists"
            :limit="10"
            :column="4"
            :widthTable="'100%'"
            @DataTable:finish="onDataTableFinished"
            ref="datatable">
            <th>EUC Box</th>
            <th>Vehicle Type</th>
            <th>Platform</th>
            <th>Action</th>
          <template slot="body" slot-scope="props">
            <tr>
              <td>
                <template v-if="props.item.editable">
                  <input
                    type="text"
                    class="input"
                    :class="{'error': errors.has(`row-${props.index + 1}.order`)}"
                    name="order"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="EUC Box"
                    v-model.trim="props.item.formInput.order"
                    v-validate="'required|numeric|min_value:1'" >
                  <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.order`)">
                    {{ errors.first(`row-${props.index + 1}.order`) }}
                  </span>
                </template>
                <template v-else>
                  <div class="text">{{ props.item.order }}</div>
                </template>
              </td>
              <td>
                <template v-if="props.item.editable">
                  <select
                    :data-vv-scope="`row-${props.index + 1}`"
                    class="input"
                    :class="{'error': errors.has(`row-${props.index + 1}.vehicle_type_id`)}"
                    v-model="props.item.formInput.vehicle_type_id"
                    name="vehicle_type_id"
                    data-vv-as="vehicle_type_id"
                    v-validate="'required'">
                    <option :value="item.id" v-for="(item, index) in vehicleTypes" :key="index">
                      {{ item.name }}
                    </option>
                  </select>
                  <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.vehicle_type_id`)">
                    {{ errors.first(`row-${props.index + 1}.vehicle_type_id`) }}
                  </span>
                </template>
                <template v-else>
                  <div class="text">{{ props.item.vehicle_type_name }}</div>
                </template>
              </td>
              <td>
                <template v-if="props.item.editable">
                  <input
                    type="text"
                    class="input"
                    :class="{'error': errors.has(`row-${props.index + 1}.platform`)}"
                    name="platform"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="Platform"
                    v-model.trim="props.item.formInput.platform"
                    v-validate="'required'" >
                  <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.platform`)">
                    {{ errors.first(`row-${props.index + 1}.platform`) }}
                  </span>
                </template>
                <template v-else>
                  <div class="text">{{ props.item.platform }}</div>
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

  </div>
</template>
<style lang="scss" scoped>
.page {
  margin: auto;
  width: 80%;
  .euc-list {
    .action {
      margin: 20px 0 10px 0;
    }
    .table-content {
      ::v-deep .box_table {
        th {
          text-align: center;
        }
        td {
          text-align: center;
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
        }
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import EucForm from './partials/EucForm'
import {chain, cloneDeep} from "lodash";
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'

const STEP = {
  ITEMS_LIST: 'items-list',
  EUC_FORM: 'euc-form'
}

export default {
  components: {
    EucForm
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      STEP,
      currentStep: STEP.ITEMS_LIST,
      data: [],
      selectedEuc: null,
      vehicleTypes: []
    }
  },

  mounted() {
    this.getVehicleTypes()
  },

  methods: {
    getEucLists(params) {
      return rf.getRequest('AdminRequest').getEucLists(params)
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

    onClickAdd () {
      const newItem = {
        editable: true,
        order: null,
        vehicle_type_name: null,
        platform: null
      }
      this.data.unshift({
        ...newItem,
        formInput: cloneDeep(newItem)
      })
    },

    onClickDelete (record, index) {
      const callback = () => {
        rf.getRequest('AdminRequest').deleteEucList({ id: record.id })
          .then(res => {
            this.showSuccess('Successfully!')
            this.$refs.datatable.refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
      }

      this.confirmAction({ callback: callback })
    },

    async onClickSave (record, index) {
      this.resetError()

      const scope = `row-${ index + 1 }`
      this.errors.clear(scope)

      await this.$validator.validate(`${scope}.*`)

      if (this.errors.any()) {
        return
      }

      this.submitRequest(record.formInput)
        .then(res => {
          this.$set(record, 'editable', false)

          const data = res.data
          this.$set(record, 'order', data.order)
          this.$set(record, 'vehicle_type_id', data.vehicle_type_id)
          this.$set(record, 'vehicle_type_name', this.getVehicleName(data.vehicle_type_id))
          this.$set(record, 'platform', data.platform)
          this.$set(record, 'id', data.id)

          this.showSuccess('Successfully!');
        })
        .catch(error => {
          this.processErrors(error, `row-${ index + 1 }`)
        })
    },

    submitRequest (data) {
      if (data.id) {
        return rf.getRequest('AdminRequest').updateEuc(data)
      }
      return rf.getRequest('AdminRequest').createEuc(data)
    },

    onClickCancel (record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${ index + 1 }.*`
      this.errors.clear(scope)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows

      chain(this.data)
          .each(row => {
            this.$set(row, 'editable', false)
            this.$set(row, 'formInput', cloneDeep(row))
          })
          .value()
    },

    getVehicleName(vehicleId) {
      let vehicle = chain(this.vehicleTypes)
          .filter((record) => {
            return record.id == vehicleId
          })
          .first()
          .value()

      return vehicle.name
    }
  }
}
</script>
