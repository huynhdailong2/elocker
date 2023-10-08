<template>
  <div class="job-card">
    <div class="table-content">
      <data-table2 :getData="getJobCards"
                   :limit="10"
                   :column="7"
                   :widthTable="'100%'"
                   @DataTable:finish="onDataTableFinished"
                   ref="datatable">
        <th>S/N</th>
        <th>Service/WO #</th>
        <th>WO#</th>
        <th>Veh#</th>
        <th>Veh Type</th>
        <th>Platform</th>
        <th>Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td>
              <div class="text">{{ props.realIndex }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`row-${props.index + 1}.card_num`)}"
                    name="card_num"
                    data-vv-as="service/mo"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="Service/WO"
                    v-model.trim="props.item.formInput.card_num"
                    v-validate="'required|numeric|min_value:1'">
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.card_num`)">
                      {{ errors.first(`row-${props.index + 1}.card_num`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.card_num }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`row-${props.index + 1}.wo`)}"
                    name="wo"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="WO#"
                    v-model.trim="props.item.formInput.wo"
                    v-validate="'required|numeric|min_value:1'">
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.wo`)">
                      {{ errors.first(`row-${props.index + 1}.wo`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.wo }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`row-${props.index + 1}`"
                    :class="{'error': errors.has(`row-${props.index + 1}.vehicle_id`)}"
                    v-model="props.item.formInput.vehicle_id"
                    name="vehicle_id"
                    data-vv-as="vehicle"
                    v-validate="'required'">
                  <option :value="item.id" v-for="(item, index) in vehicles" :key="index">{{
                      item.vehicle_num
                    }}
                  </option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.vehicle_id`)">
                      {{ errors.first(`row-${props.index + 1}.vehicle_id`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.vehicle_num }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ getVehicleType(props.item.formInput, props.index) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`row-${props.index + 1}.platform`)}"
                    name="platform"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="Platform"
                    v-model.trim="props.item.formInput.platform"
                    v-validate="'required'">
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
                <img src="/images/icons/icon-trash.svg" width="22px"
                     @click.stop="onClickDelete(props.item, props.index)">
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
$widthCell: 100%;

.job-card {
  .action {
    margin: 20px 0 10px 0;
  }

  .table-content {
    ::v-deep .box_table {
      th {
        text-align: center;
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
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import {chain, cloneDeep} from 'lodash'

export default {
  data() {
    return {
      data: [],
      vehicles: []
    }
  },

  mounted() {
    this.getVehicles()
  },

  methods: {
    getJobCards(params) {
      return rf.getRequest('AdminRequest').getClosedJobCards(params)
    },

    getVehicles() {
      const params = {
        no_pagination: true
      }
      rf.getRequest('VehicleRequest').getVehicles(params)
          .then(res => {
            this.vehicles = res.data || []
          })
    },

    onDataTableFinished() {
      this.data = this.$refs.datatable.rows

      chain(this.data)
          .each(row => {
            this.$set(row, 'editable', false)
            this.$set(row, 'formInput', cloneDeep(row))
          })
          .value()
    },

    onClickCancel(record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)
    },

    onClickDelete(record, index) {
      const refresh = () => {
        this.$refs.datatable.refresh()
      }

      if (!record.id) {
        refresh()
      }

      rf.getRequest('AdminRequest').deleteJobCard({id: record.id})
          .then(res => {
            this.showSuccess('Successfully!');
            refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
    },

    async onClickSave(record, index) {
      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)

      await this.$validator.validate(scope)

      if (this.errors.any()) {
        return
      }

      this.submitRequest(record.formInput)
          .then(res => {
            this.$set(record, 'editable', false)
            const data = res.data

            this.$set(record, 'card_num', data.card_num)
            this.$set(record, 'wo', data.wo)
            this.$set(record, 'vehicle_id', data.vehicle_id)
            this.$set(record, 'vehicle_num', data.vehicle_num)
            this.$set(record, 'platform', data.platform)

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${index + 1}`)
          })
    },

    submitRequest(data) {
      if (data.id) {
        return rf.getRequest('AdminRequest').updateJobCard(data)
      }
      return rf.getRequest('AdminRequest').createJobCard(data)
    },

    getVehicleType(record, index) {
      if (!record.vehicle_id) {
        return null
      }

      const vehicle = chain(this.vehicles)
          .find(item => item.id === record.vehicle_id)
          .value()

      return vehicle ? vehicle.vehicle_type_name : null
    }
  }
}
</script>
