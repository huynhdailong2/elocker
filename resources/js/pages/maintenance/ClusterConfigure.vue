<template>
  <div class="block shelf-configure">
    <div class="action">
      <button class="btn-primary" @click.stop="onClickAddNewCluster">Add New Cluster</button>
    </div>
    <div class="table-content">
      <data-table2 :getData="getClusters"
          :limit="10"
          :column="6"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">S/N</th>
          <th class="text-center">Cluster</th>
          <th class="text-center">Code</th>
          <th class="text-center">Status</th>
          <th class="text-center">Is RFID</th>
          <th class="text-center">Is Virtual</th>
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
                  placeholder="Cluster Name"
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
            <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`row-${props.index + 1}.name`)}"
                  name="code"
                  :data-vv-scope="`row-${props.index + 1}`"
                  placeholder="Code"
                  v-model.trim="props.item.formInput.code" >
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.code`)">
                  {{ errors.first(`row-${props.index + 1}.code`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.code }}</div>
              </template>
            </td>
            <td>
              <div v-if="props.item.is_online" class="text online">Online</div>
              <div v-else class="text offline">Offline</div>
            </td>
            <!-- <input type="checkbox" class="checkbox" v-model="item.checked"> -->
            <td>
              <template v-if="props.item.editable">
                <label class="checkbox-container">
                  <input
                    type="checkbox"
                    :class="{'error': errors.has(`row-${props.index + 1}.is_rfid`)}"
                    name="is_rfid"
                    :data-vv-scope="`row-${props.index + 1}`"
                    v-model="props.item.formInput.is_rfid" >
                  <span class="checkmark"></span>
                </label>
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.is_rfid`)">
                  {{ errors.first(`row-${props.index + 1}.is_rfid`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.is_rfid ? "Yes" : "No" }}</div>
              </template>
            </td>
            <td class="action">
              <div class="text-center">
                <template v-if="props.item.editable">
                  <img src="/images/icons/icon-cancel.svg" width="22px">
                </template>
                <template v-else>
                  <template v-if="props.item.is_virtual">
                    <img src="/images/icons/icon-save.svg" width="22px"
                         @click.stop="onToggleIsVirtual(props.item, props.index)">
                  </template>
                  <template v-else>
                    <img src="/images/icons/icon-cancel.svg" width="22px"
                         @click.stop="onToggleIsVirtual(props.item, props.index)">
                  </template>
                </template>
              </div>
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

.shelf-configure {
  width: 100% !important;
  .action {
    margin: 20px 0 10px 0;
  }
  .table-content {
    ::v-deep .box_table {
      td {
        padding: 0px;
        input, select {
          height: $heightCell;
          width: $widthCell;
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
        &.row-check {
          text-align: center;
          vertical-align: middle;
        }
        .text {
          vertical-align: middle;
          text-align: center;
          line-height: 38px;
        }
        .online {
          color: #4b4bff;
          font-weight: bold;
        }
        .offline {
          color: #c41d1d;
          font-weight: bold;
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
  import { chain, cloneDeep, remove } from 'lodash'

  export default {
    mixins: [RemoveErrorsMixin],

    data () {
      return {
        data: [],
        clusters: [],
      }
    },

    mounted () {
      this.getClusters()
    },

    computed: {
      types () {
        return Object.values(Const.SHELF_TYPE)
      },
    },

    methods: {
      getClusters(params) {
        return rf.getRequest('AdminRequest').getClusters(params)
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

      onClickAddNewCluster () {
        const newItem = {
          editable: true,
          name: null,
          code: null,
          is_rfid: false,
          is_virtual: 0,
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

        const callback = () => {
          rf.getRequest('AdminRequest').deleteCluster({ id: record.id })
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

            this.$refs.datatable.refresh()

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${ index + 1 }`)
          })
      },

      submitRequest (data) {
        if (data.id) {
          return rf.getRequest('AdminRequest').updateCluster(data)
        }
        return rf.getRequest('AdminRequest').createCluster(data)
      },

      onToggleIsVirtual(item) {
        const params = cloneDeep(item)
        params.is_virtual = item.is_virtual ? 0 : 1
        rf.getRequest('AdminRequest').updateVirtualCluster({
          is_virtual: params.is_virtual,
          id: params.id
        })
          .then(res => {
            this.showSuccess('Successfully!');
            this.$refs.datatable.refreshCurrentPage()
            this.resetError()
          })
          .catch(error => {
            this.processErrors(error)
          })
      },
    }
  }
</script>
