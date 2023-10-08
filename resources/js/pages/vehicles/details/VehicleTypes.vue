<template>
  <div class="vehicle-types">
    <div class="action" v-if="canAddMore">
      <button class="btn-primary" @click.stop="onClickAddNew">Add Vehicle Type</button>
    </div>
    <div class="table-content">
      <data-table2 :getData="getVehicleTypes"
          :limit="10"
          :column="3"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">S/N</th>
          <th class="text-center">Name</th>
          <th>Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td><div class="text">{{ props.realIndex }}</div></td>
            <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`row-${props.index + 1}.name`)}"
                  name="name"
                  :data-vv-scope="`row-${props.index + 1}`"
                  placeholder="Name"
                  v-model.trim="props.item.formInput.name"
                  v-validate="'required'" >
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.name`)">
                  {{ errors.first(`row-${props.index + 1}.name`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.name }}</div>
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
  $widthCell: 100px;

  .vehicle-types {
    width: 100%;
    .action {
      margin: 20px 0 10px 0;
    }
    .table-content {
      ::v-deep .box_table {
        td {
          padding: 0px;
          input {
            height: $heightCell;
            min-width: $widthCell;
            width: 100%;
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
  import moment from 'moment'
  import rf from 'requestfactory'
  import { mapState } from 'vuex'
  import Const from 'common/Const'
  import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
  import { chain, cloneDeep, remove, size } from 'lodash'

  export default {
    data () {
      return {
        data: [],
        shelfs: [],
        spares: [],
      }
    },

    computed: {
      length () {
        return size(this.data)
      },

      canAddMore () {
        return true;
        ///return this.length < 3
      }
    },

    methods: {
      getVehicleTypes(params) {
        return rf.getRequest('VehicleRequest').getVehicleTypes(params);
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

      onClickAddNew () {
        if (!this.canAddMore) {
          return
        }

        const newItem = {
          editable: true,
          name: null,
        }
        this.data.push({
          ...newItem,
          formInput: cloneDeep(newItem)
        })
      },

      onClickCancel (record, index) {
        this.$set(record, 'editable', false)
        this.$set(record, 'formInput', cloneDeep(record))

        const scope = `row-${ index + 1 }.*`
        this.errors.clear(scope)
      },

      onClickDelete (record, index) {
        const refresh = () => {
          this.$refs.datatable.refresh()
        }

        if (!record.id) {
          refresh()
        }

        rf.getRequest('VehicleRequest').deleteVehicleType({ id: record.id })
          .then(res => {
            this.showSuccess('Successfully!');
            refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
      },

      async onClickSave (record, index) {
        const scope = `row-${ index + 1 }.*`
        this.errors.clear(scope)

        await this.$validator.validate(scope)

        if (this.errors.any()) {
          return
        }

        this.submitRequest(record.formInput)
          .then(res => {
            this.$set(record, 'editable', false)

            this.$set(record, 'name', res.data.name)

            this.showSuccess('Successfully!');
            this.$emit('updated', res.data)
          })
          .catch(error => {
            this.processErrors(error, `row-${ index + 1 }`)
          })
      },

      submitRequest (data) {
        if (data.id) {
          return rf.getRequest('VehicleRequest').updateVehicleType(data)
        }
        return rf.getRequest('VehicleRequest').createVehicleType(data)
      }
    }
  }
</script>
