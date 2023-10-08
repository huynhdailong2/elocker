<template>
  <div class="block bin-configure">
    <div class="filter">
      <div class="option">
        <label>Cluster:</label>
        <select class="input" v-model="filters.cluster_id" @change='getShelfs()'>
          <option value="">All</option>
          <option :value="item.id" v-for="(item, index) in clusters" :key="index">{{ item.name }}</option>
        </select>
      </div>
      <div class="option">
        <label>Shelf:</label>
        <select class="input" v-model="filters.shelf_id">
          <option value="">All</option>
          <option :value="item.id" v-for="(item, index) in shelfs" :key="index">{{ item.name }}</option>
        </select>
      </div>
      <div class="option">
        <label>Row:</label>
        <select class="input" v-model="filters.row">
          <option value="">All</option>
          <option :value="item" v-for="(item, index) in rows" :key="index">{{ item }}</option>
        </select>
      </div>
      <div class="option">
        <label>Bin:</label>
        <select class="input" v-model="filters.bin">
          <option value="">All</option>
          <option :value="item" v-for="(item, index) in bins" :key="index">{{ item }}</option>
        </select>
      </div>
      <div class="option">
        <label>Status:</label>
        <select class="input" v-model="filters.status">
          <option value="">All</option>
          <option :value="item.value" v-for="(item, index) in statusList" :key="index">{{ item.name }}</option>
        </select>
      </div>
      <div class="option">
        <label>Is Drawer:</label>
        <select class="input" v-model="filters.is_drawer">
          <option value="">All</option>
          <option :value="item.value" v-for="(item, index) in drawerStatuses" :key="index">{{ item.name }}</option>
        </select>
      </div>
      <div class="option">
        <label>Search:</label>
        <input type="text" v-model="searchKey" placeholder="" />
      </div>
    </div>
    <div class="table-content">
      <data-table2 :getData="getBins"
          :limit="14"
          :column="14"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">S/N</th>
          <th class="text-center">Cluster</th>
          <th class="text-center">Shelf</th>
          <th class="text-center">Row</th>
          <th class="text-center">Bin</th>
          <th class="text-center">Drawer</th>
          <th class="ml-2">Item Name</th>
          <th class="text-center">Item Type</th>
          <th class="text-center">Calibration Due/Inspection</th>
          <th class="text-center">Expiry Date</th>
          <th class="text-center">Item P/N</th>
          <th class="text-center">Is Drawer</th>
          <th class="text-center">Is Failed</th>
          <th>Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td><div class="text">{{ props.realIndex }}</div></td>
            <td><div class="text">{{ props.item.cluster_name }}</div></td>
            <td><div class="text">{{ props.item.shelf_name }}</div></td>
            <td><div class="text">{{ props.item.row }}</div></td>
            <td><div class="text">{{ props.item.bin }}</div></td>
            <td><div class="text">{{ props.item.drawer_name }}</div></td>
            <td><div class="ml-2">{{ props.item.spare_name }}</div></td>
            <td><div class="text-center">{{ getLabelByType(props.item.type) }}</div></td>
            <td><div class="text">{{ getValue(props.item, 'calibration_due') | dateFormatter('YYYY-MM-DD') }}</div></td>
            <td><div class="text">{{ getValue(props.item, 'expiry_date') | dateFormatter('YYYY-MM-DD') }}</div></td>
            <td><div class="text">{{ props.item.part_no }}</div></td>
            <td class="action">
              <div class="text-center">
                <template v-if="props.item.is_drawer">
                  <img src="/images/icons/icon-save.svg" width="22px"
                    @click.stop="onToggleIsDrawer(props.item, props.index)">
                </template>
                <template v-else>
                  <img src="/images/icons/icon-cancel.svg" width="22px"
                    @click.stop="onToggleIsDrawer(props.item, props.index)">
                </template>
              </div>
            </td>
            <td class="action">
              <div class="text-center">
                <template v-if="props.item.is_failed">
                  <img src="/images/icons/icon-save.svg" width="22px"
                       @click.stop="onToggleIsFailed(props.item, props.index)">
                </template>
                <template v-else>
                  <img src="/images/icons/icon-cancel.svg" width="22px"
                       @click.stop="onToggleIsFailed(props.item, props.index)">
                </template>
              </div>
            </td>
            <td class="action">
              <img src="/images/icons/icon-edit.svg" width="22px" @click.stop="onClickEdit(props.item)">
              <img src="/images/icons/icon-cancel.svg" width="22px" @click.stop="onClickUnAssigned(props.item)">
            </td>
          </tr>
        </template>
      </data-table2>
    </div>

    <edit-bin-modal />

  </div>
</template>
<style lang="scss" scoped>
$heightCell: 40px;
$widthCell: 100px;

.bin-configure {
  width: 100% !important;
  .filter {
    margin: 20px 0 10px 0;
    display: flex;
    .option {
      margin-right: 30px;
      &:last-child {
        margin-right: 0;
      }
      select {
        width: 150px;
      }
      input {
        // width: 100%;
        max-width: 150px;
        padding: 5px;
      }
    }
  }
  .table-content {
    ::v-deep .box_table {
      td {
        padding: 0px;
        vertical-align: middle;
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
import rf from 'requestfactory'
import { chain, cloneDeep, times, debounce, head } from 'lodash'
import EditBinModal from './partial/EditBinModal'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import Const from 'common/Const'

export default {
  components: {
    EditBinModal
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      data: [],
      clusters: [],
      shelfs: [],
      spares: [],
      filters: {
        cluster_id: '',
        shelf_id: '',
        row: '',
        bin: '',
        status: '',
        is_drawer: '',
      },
      searchKey: null
    }
  },

  computed: {
    selectedCluster () {
      const cluster_id = chain(this.clusters)
        .find(item => item.id === this.filters.cluster_id)
        .value()

      return cluster_id
    },

    selectedShelf () {
      return chain(this.shelfs)
        .find(item => item.id === this.filters.shelf_id)
        .value()
    },

    rows () {
      if (!this.selectedShelf) {
        return []
      }

      return times(this.selectedShelf.num_rows, (number) => number + 1)
    },

    bins () {
      if (!this.selectedShelf) {
        return []
      }
      return times(this.selectedShelf.num_bins, (number) => number + 1)
    },

    statusList () {
      return [
        { value: 'unassigned', name: 'Unassigned' },
        { value: 'assigned', name: 'Assigned' },
        { value: 'is_failed', name: 'Is Failed' },
      ]
    },

    drawerStatuses () {
      return [
        { value: '0', name: 'No' },
        { value: '1', name: 'Yes' }
      ]
    }
  },

  watch: {
    filters: {
      deep: true,
      handler: debounce(function () {
        this.$refs.datatable.refresh()
      }, 300)
    },

    searchKey: {
      deep: true,
      handler: debounce(function () {
        this.$refs.datatable.refresh()
      }, 300)
    }
  },

  mounted () {
    this.getClusters()
  },

  methods: {
    getClusters(params) {
      params = {
        ...params,
        no_pagination: true
      }
      rf.getRequest('AdminRequest').getClusters(params)
        .then(res => {
          this.clusters = res.data || []
        })
    },

    getShelfs(params) {
      params = {
        ...params,
        cluster_id: this.filters.cluster_id,
        no_pagination: true
      }
      rf.getRequest('AdminRequest').getShelfs(params)
        .then(res => {
          this.shelfs = res.data || []
        })
    },

    getBins(params) {
      params = {
        ...params,
        ...this.filters,
        search_key: this.searchKey,
      }
      return rf.getRequest('AdminRequest').getBins(params)
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
    onClickEdit (item) {
      this.showEditBinModal(item)
    },

    onToggleIsDrawer(item, idx) {
      const params = cloneDeep(item)
      params.is_drawer = !item.is_drawer
      rf.getRequest('AdminRequest').patchBin(params)
        .then(res => {
          this.showSuccess('Successfully!');
          this.$refs.datatable.refreshCurrentPage()
          this.resetError()
        })
        .catch(error => {
          this.processErrors(error)
        })
    },

    onToggleIsFailed(item, idx) {
      const params = cloneDeep(item)
      params.is_failed = !item.is_failed
      rf.getRequest('AdminRequest').patchBin(params)
          .then(res => {
            this.showSuccess('Successfully!');
            this.$refs.datatable.refreshCurrentPage()
            this.resetError()
          })
          .catch(error => {
            this.processErrors(error)
          })
    },

    onClickUnAssigned (record) {
      const _handler = () => {
        rf.getRequest('AdminRequest').unassignedBin({ id: record.id })
          .then(res => {
            this.showSuccess('Successful!')
            this.$refs.datatable.refresh()
          })
          .catch(error => {
            // this.processErrors(error)
            this.processAndToastFirstError(error)
          })
      }

      this.confirmAction({ callback: _handler })
    },

    showEditBinModal (bin) {
      const callback = () => {
        // this.$refs.datatable.refresh();
        this.$refs.datatable.refreshCurrentPage();
      }

      this.$modal.show('edit-bin-modal', { bin, callback })
    },

    getValue (record, property) {
      const item = head(record.configures || [])
      return item ? item[property] : ''
    },

    getLabelByType(type) {
      let matchType = chain(Const.ITEM_TYPE)
          .filter((record) => {
            return record.value == type
          })
          .head()
          .value()

      return matchType ? matchType.name : type;
    }
  }
}
</script>
