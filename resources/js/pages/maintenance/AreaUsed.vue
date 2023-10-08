<template>
  <div class="page">
    <div class="content">
      <div class="title-page">
        <div class="text-center">Area Used</div>
      </div>
      <div class="area-used block">
        <div class="action">
          <button class="btn-primary" @click.stop="onClickAddNew">Add Area Used</button>
        </div>
        <div class="table-content">
          <data-table2 :getData="getAreaUsed"
              :limit="10"
              :column="3"
              :widthTable="'100%'"
              @DataTable:finish="onDataTableFinished"
              ref="datatable">
              <th>S/N</th>
              <th>Area</th>
              <th>Torque(N.M)</th>
              <th>Action</th>
            <template slot="body" slot-scope="props">
              <tr>
                <td><div class="text">{{ props.realIndex }}</div></td>
                <td>
                  <template v-if="props.item.editable">
                    <input
                      type="text"
                      class="input_g"
                      :class="{'error': errors.has(`row-${props.index + 1}.area`)}"
                      name="area"
                      data-vv-as="service/mo"
                      :data-vv-scope="`row-${props.index + 1}`"
                      placeholder="Area"
                      v-model.trim="props.item.formInput.area"
                      v-validate="'required'" >
                    <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.area`)">
                      {{ errors.first(`row-${props.index + 1}.area`) }}
                    </span>
                  </template>
                  <template v-else>
                    <div class="text">{{ props.item.area }}</div>
                  </template>
                </td>
                <td>
                  <template v-if="props.item.editable">
                    <input
                      type="text"
                      class="input_g"
                      :class="{'error': errors.has(`row-${props.index + 1}.torque_value`)}"
                      name="torque_value"
                      :data-vv-scope="`row-${props.index + 1}`"
                      placeholder="Torque(N.M)"
                      v-model.trim="props.item.formInput.torque_value"
                      v-validate="'required|numeric|min_value:0'" >
                    <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.torque_value`)">
                      {{ errors.first(`row-${props.index + 1}.torque_value`) }}
                    </span>
                  </template>
                  <template v-else>
                    <div class="text">{{ parseFloat(props.item.torque_value) }}</div>
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
  </div>
</template>
<style lang="scss" scoped>
$heightCell: 40px;
$widthCell: 100%;

  .area-used {
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
    data () {
      return {
        data: [],
      }
    },

    methods: {
      getAreaUsed(params) {
        let areaUsed = rf.getRequest('AdminRequest').getTorqueWrenchAreas(params)

        return areaUsed;
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
        const newItem = {
          editable: true,
          area: null,
          torque_value: null,
        }
        this.data.unshift({
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

        const callback = () => {
          rf.getRequest('AdminRequest').deleteTorqueWrenchArea({id: record.id})
            .then(res => {
              this.showSuccess('Successfully!');
              refresh()
            })
            .catch(error => {
              this.processErrors(error)
            })
        }

        this.confirmAction({ callback })
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
            const data = res.data

            this.$set(record, 'area', data.area)
            this.$set(record, 'torque_value', data.torque_value)

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${ index + 1 }`)
          })
      },

      submitRequest (data) {
        if (data.id) {
          return rf.getRequest('AdminRequest').updateTorqueWrenchArea(data)
        }
        return rf.getRequest('AdminRequest').createTorqueWrenchArea(data)
      },
    }
  }
</script>
