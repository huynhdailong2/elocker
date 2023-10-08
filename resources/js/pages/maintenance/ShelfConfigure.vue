<template>
  <div class="block shelf-configure">
    <div class="action">
      <button class="btn-primary" @click.stop="onClickAddNewShelf">Add New Shelf</button>
    </div>
    <div class="table-content">
      <data-table2 :getData="getShelfs"
          :limit="10"
          :column="7"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">S/N</th>
          <th class="text-center">Cluster</th>
          <th class="text-center">Shelf Name</th>
          <th class="text-center">Number of Row</th>
          <th class="text-center">Number of Bin</th>
          <th class="text-center">Type</th>
          <th>Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td><div class="text">{{ props.realIndex }}</div></td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`row-${props.index + 1}`"
                    :class="{'error': errors.has(`row-${props.index + 1}.cluster_id`)}"
                    v-model="props.item.formInput.cluster_id"
                    name="cluster_id"
                    data-vv-as="cluster"
                    v-validate="'required'" >
                  <option :value="item.id" v-for="(item, index) in clusters" :key="index">{{ item.name }}</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.cluster_id`)">
                  {{ errors.first(`row-${props.index + 1}.cluster_id`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.cluster_name }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`row-${props.index + 1}.name`)}"
                  name="name"
                  :data-vv-scope="`row-${props.index + 1}`"
                  placeholder="Shelf Name"
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
                  :class="{'error': errors.has(`row-${props.index + 1}.rows`)}"
                  name="rows"
                  :data-vv-scope="`row-${props.index + 1}`"
                  placeholder="Rows"
                  v-model.trim="props.item.formInput.num_rows"
                  v-validate="'required|numeric|min_value:1'" >
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.rows`)">
                  {{ errors.first(`row-${props.index + 1}.rows`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.num_rows }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has(`row-${props.index + 1}.bins`)}"
                  name="bins"
                  :data-vv-scope="`row-${props.index + 1}`"
                  placeholder="Bins"
                  v-model.trim="props.item.formInput.num_bins"
                  v-validate="'required|numeric|min_value:1'" >
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.bins`)">
                  {{ errors.first(`row-${props.index + 1}.bins`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.num_bins }}</div>
              </template>
            </td>
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`row-${props.index + 1}`"
                    :class="{'error': errors.has(`row-${props.index + 1}.type`)}"
                    v-model="props.item.formInput.type"
                    name="type"
                    data-vv-as="type"
                    v-validate="'required'" >
                  <option :value="item.value" v-for="(item, index) in types" :key="index">{{ item.name }}</option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.type`)">
                  {{ errors.first(`row-${props.index + 1}.type`) }}
                </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.shelf_type }}</div>
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
      getClusters() {
        const params = {
          no_pagination: true
        }
        rf.getRequest('AdminRequest').getClusters(params)
          .then(res => {
            this.clusters = res.data || []
          })
      },

      getShelfs(params) {
        return rf.getRequest('AdminRequest').getShelfs(params)
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

      onClickAddNewShelf () {
        const newItem = {
          editable: true,
          name: null,
          num_rows: null,
          num_bins: null
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

        rf.getRequest('AdminRequest').deleteShelf({ id: record.id })
          .then(res => {
            this.showSuccess('Successfully!');
            refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
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
            // const data = res.data
            // this.$set(record, 'cluster_id', data.cluster_id)
            // this.$set(record, 'cluster_name', data.cluster_name)
            // this.$set(record, 'name', data.name)
            // this.$set(record, 'num_rows', data.num_rows)
            // this.$set(record, 'num_bins', data.num_bins)
            // this.$set(record, 'type', data.type)
            // this.$set(record, 'shelf_type', data.shelf_type)

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${ index + 1 }`)
          })
      },

      submitRequest (data) {
        if (data.id) {
          return rf.getRequest('AdminRequest').updateShelf(data)
        }
        return rf.getRequest('AdminRequest').createShelf(data)
      }
    }
  }
</script>
