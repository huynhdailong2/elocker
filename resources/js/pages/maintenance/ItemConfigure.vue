<template>
  <div class="items">
    <div class="list">
      <div class="block">
        <div class="d-flex mb-3">
          <div class="action">
            <button class="btn-primary" @click.stop="onClickAdd">Add Item</button>
            <button class="btn-primary" @click.stop="onClickImport">Import Items</button>
            <button class="btn-primary" @click.stop="onClickExport">Export Items</button>
          </div>
          <div class="filter">
            <input
              type="text"
              class="input"
              placeholder="Search Part No ..."
              v-model="inputSearch" />
          </div>
        </div>

        <div class="table-content">
          <data-table2 :getData="getSpares"
              :limit="10"
              :column="7"
              :widthTable="'100%'"
              @DataTable:finish="onDataTableFinished"
              ref="datatable">
              <th class="text-center">S/N</th>
              <th class="text-center mw_110px maw_145x">Item Name</th>
              <th class="text-center">Type</th>
              <th class="text-center">P/N</th>
              <th class="text-center mw_110px maw_145x">Mat’l No</th>
              <th class="mw_110px maw_145x">Description</th>
              <th>Action</th>
            <template slot="body" slot-scope="props">
              <tr>
                <td><div class="text">{{ props.realIndex }}</div></td>
                <td class="mw_110px maw_145x" :title="props.item.name" >
                  <div class="text ellipsis">{{ props.item.name }}</div>
                </td>
                <td class="mw_110px maw_145x" :title="props.item.type | uppercaseFirst" >
                  <div class="text ellipsis">{{ props.item.type | formatItemType }}</div>
                </td>
                <td class="mw_110px maw_145x" :title="props.item.part_no" >
                  <div class="text ellipsis">{{ props.item.part_no }}</div>
                </td>
                <td class="mw_110px maw_145x" :title="props.item.material_no" >
                  <div class="text ellipsis">{{ props.item.material_no }}</div>
                </td>
                <td class="mw_110px maw_145x" :title="props.item.description" >
                  <div class="ellipsis">{{ props.item.description }}</div>
                </td>
                <td class="action">
                  <img src="/images/icons/icon-edit.svg" width="22px" @click.stop="onClickEdit(props.item, props.index)">
                  <img src="/images/icons/icon-trash.svg" width="22px" @click.stop="onClickDelete(props.item, props.index)">
                </td>
              </tr>
            </template>
          </data-table2>
        </div>
      </div>

      <item-form-modal />
      <items-import-modal @closed="onSparesImporting"/>

    </div>
  </div>
</template>
<style lang="scss" scoped>
$heightCell: 40px;
$widthCell: 100px;

.items {
  .list {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    .block {
      margin: 15px;
      padding: 15px;
      background-color: #212430;
      color: #fff;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
      position: relative;
      z-index: 1;
      .table-content {
        ::v-deep .box_table {
          td {
            vertical-align: middle;
            padding: 0px 10px;
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
          .min_w_100 {
            min-width: 100px;
          }
        }
      }
      .filter {
        display: flex;
        width: 100%;
        justify-content: flex-end;
        .input {
          width: 300px;
        }
      }
      .action {
        display: flex;
        width: 100%;
        justify-content: flex-start;
        gap: 5px;
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
  import {chain, cloneDeep, debounce, remove} from 'lodash'
  import ItemFormModal from './partial/ItemFormModal'
  import ItemsImportModal from './partial/ItemsImportModal'

  export default {
    components : {
      ItemFormModal,
      ItemsImportModal
    },

    mixins: [RemoveErrorsMixin],

    data () {
      return {
        data: [],
        shelfs: [],
        spares: [],
        inputSearch: null,
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
      this.getShelfs()
    },

    methods: {
      getShelfs(params) {
        params = {
          ...params,
          no_pagination: true
        }
        rf.getRequest('AdminRequest').getShelfs(params)
          .then(res => {
            this.shelfs = res.data || []
          })
          .catch(error => {
            this.processErrors(error)
          })
      },

      getSpares(params) {
        params = {
          ...params,
          search_key: this.inputSearch,
        }
        return rf.getRequest('AdminRequest').getSpares(params)
      },

      onDataTableFinished () {
        this.data = this.$refs.datatable.rows

        chain(this.data)
          .each(row => {
            const usersAccessing = chain(row.user_accessing_spares)
              .map(item => item.role)
              .value()
            this.$set(row, 'user_access', usersAccessing)
          })
          .value()
      },

      onClickEdit (record, index) {
        this.showItemFormModal(record)
      },

      onClickDelete (record, index) {
        const refresh = () => {
          this.$refs.datatable.refresh()
        }

        if (!record.id) {
          refresh()
        }

        const callback = () => {
          rf.getRequest('AdminRequest').deleteSpare({ id: record.id })
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

      onClickAdd () {
        this.showItemFormModal()
      },

      onClickImport () {
        this.$modal.show('items-import-modal')
      },

      onClickExport() {
        return rf.getRequest('AdminRequest').exportSpares();
      },

      showItemFormModal (spare =  null) {
        const callback = () => {
          this.$refs.datatable.refresh();
        }

        this.$modal.show('item-form-modal', { spare, callback })
      },

      onSparesImporting (complete) {
        this.$nextTick(() => {
          !!complete && this.$refs.datatable.refresh()
        })
      }
    }
  }
</script>
