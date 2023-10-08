<template>
  <div class="table" :style="{width: widthTable}">
    <div class="box_table">
      <table>
        <thead>
        <tr @click="onSort">
          <slot/>
        </tr>
        </thead>
        <tbody>
        <slot name="first_row"/>
        <slot name="body" v-for="(row, index, key) in rows"
          :item="row"
          :index="index"
          :keyData="key"
          :realIndex="getRealIndex(index, key)" />
        <!-- <template v-for="row in emptyRow">
          <tr>
            <template v-for="col in column">
              <td></td>
            </template>
          </tr>
        </template> -->
        <template v-if="this.rows.length === 0">
            <tr class="empty-data"><td :colspan="column">
              <p>
                <span class="icon-notfound3"></span>
                <span>{{ msgEmptyData || 'There is no data.' }}</span>
              </p>

            </td></tr>
          </template>
        <slot name="end_row"/>
        </tbody>
      </table>
    </div>
    <template>
      <pagination ref="pagination"
                  class="text-center"
                  :per-page="perPage"
                  :records="totalRecord"
                  :chunk="chunk"
                  @change-limit="onChangeLimit($event)"
                  @Pagination:page="onPageChange" :pageParent="page">
      </pagination>
    </template>
  </div>
</template>

<script>
  import Pagination from './Pagination';

  export default {
    components: {
      Pagination
    },
    props: {
      getData: {
        type: Function,
      },
      limit: {
        type: Number,
        default: 10
      },
      column: {
        type: Number,
        default: 0
      },
      chunk: {
        type: Number,
        default: 6
      },
      widthTable: {
        type: String,
        default: '100%'
      },
      msgEmptyData: {
        type: String
      },
      specifyHidePagination: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        visiblePagination: false,
        internalLimit: 0,
        maxPageWidth: 10,
        totalRecord: 0,
        lastPage: 0,
        page: 1,
        perPage: 10,
        fetching: false,
        rows: [],
        params: {},

        orderBy: null,
        sortedBy: null,
      };
    },
    computed: {
      emptyRow() {
        let emptyRowCount = Math.max(this.internalLimit - _.size(this.rows), 0);
        return Math.min(emptyRowCount, this.internalLimit);
      }
    },
    watch: {
      limit(newValue) {
        this.internalLimit = newValue;
      }
    },
    methods: {
      getRealIndex(index, key) {
        index = isNaN(index) ? key : index

        return (this.page - 1) * this.limit + index + 1;
      },

      onChangeLimit(limit) {
        this.visiblePagination = true;
        this.internalLimit = limit;
        this.refresh();
      },

      onPageChange(page) {
        this.page = page;
        this.fetch();
      },

      getTarget(target) {
        let node = target;
        while (node.parentNode.nodeName !== 'TR') {
          node = node.parentNode;
        }
        return node;
      },

      getSortOrder(target) {
        let sortOrder = target.dataset.sortOrder;
        switch (sortOrder) {
          case 'asc':
            sortOrder = '';
            break;
          case 'desc':
            sortOrder = 'asc';
            break;
          default:
            sortOrder = 'desc';
        }
        return sortOrder;
      },

      setSortOrders(target, sortOrder) {
        let iterator = target.parentNode.firstChild;
        while (iterator) {
          iterator.dataset.sortOrder = '';
          iterator = iterator.nextElementSibling;
        }
        target.dataset.sortOrder = sortOrder;
      },

      onSort(event) {
        const target = this.getTarget(event.target);
        const orderBy = target.dataset.sortField;
        if (!orderBy) {
          return
        }
        this.sortedBy = this.getSortOrder(target);
        this.orderBy = this.sortedBy ? orderBy : '';
        Object.assign(this.params, {sort: this.orderBy, sort_type: this.sortedBy});
        this.setSortOrders(target, this.sortedBy);
        this.fetch();
      },

      /*
       * Fetch data after remove a item in datatable.
      */
      fetchAfterDataChanged() {
        const isFetchPreviousPage = (this.totalRecord % this.internalLimit) === 1;
        if (!isFetchPreviousPage) {
          return this.fetch();
        }
        let newPage = 1;
        if (this.page > 2) {
          newPage = this.page - 1;
        }
        this.fetch(newPage);
      },

      fetch(page) {
        const meta = {
          page    : page || this.page,
          limit   : this.internalLimit
        };

        this.fetching = true;
        this.getData(Object.assign(meta, this.params)).then((res) => {
          const data = res.data;
          if (!data) {
            return;
          }
          if (!data.data) {
            this.rows = data;
            this.page = parseInt(data.current_page) ? parseInt(data.current_page) : parseInt(res.current_page);
            this.totalRecord = parseInt(data.total) ? parseInt(data.total) : parseInt(res.total) ;
            this.lastPage = parseInt(data.last_page) ? parseInt(data.last_page) :  parseInt(res.last_page);
            this.perPage = parseInt(data.per_page) ? parseInt(data.per_page) : parseInt(res.per_page);
            this.$emit('DataTable:finish');
            return;
          }
          this.page = parseInt(data.current_page);
          this.totalRecord = parseInt(data.total);
          this.lastPage = parseInt(data.last_page);
          this.perPage = parseInt(data.per_page);
          this.rows = data.data;
          this.$emit('DataTable:finish');
        }).then((res) => {
          this.fetching = false;
        });
      },

      refresh() {
        this.page = 1;
        this.params = {};
        this.fetch();
      },

      refreshCurrentPage() {
        this.fetch();
      },

      filter(params) {
        this.page = 1;
        this.params = params;
        this.fetch();
      },

      setRows(rows) {
        this.rows = rows;
      }
    },
    created() {
      this.internalLimit = this.limit;
      this.fetch();
      this.$on('DataTable:filter', (params) => {
        this.filter(params);
      });
    }
  };
</script>

<style lang="scss" scoped>
  @import "./../../../sass/_variables";

  .table {
    margin-bottom: 0px;

    .box_table {
      background-color: #212430;
      min-height: 328px;
      position: relative;
      overflow: auto;
      border: 1px solid #363A47;
      border-bottom: none;
      // &::after {
      //   content: "";
      //   display: block;
      //   width: 2px;
      //   height: 100%;
      //   background-color: #D7D7D7;
      //   position: absolute;
      //   left: 0px;
      //   top: 0px;
      // }

      // &::before {
      //   content: "";
      //   display: block;
      //   width: 2px;
      //   height: 100%;
      //   background-color: #D7D7D7;
      //   position: absolute;
      //   right: 0px;
      //   top: 0px;
      // }

      table {
        border-collapse: collapse;
        width: 100%;
        color: #fff;

        thead {
          background-color: #212430;

          th {
            border: 1px solid #363A47;
            font-weight: bold;
            font-size: 16px;
            line-height: 22px;
            padding: 21px 9px;
            &::after{
            }

            &[data-sort-field] {
              cursor: pointer;

              &::before {
                content: url("/images/icons/icon-sort.svg");
                display: inline-block;
                float: left;
                width: 22px;
                height: 22px;
                margin-right: 5px;
              }
            }

            &[data-sort-order=asc] {

              &::before {
                content: url("/images/icons/icon-sort-asc.svg");
              }
            }

            &[data-sort-order=desc] {

              &::before {
                content: url("/images/icons/icon-sort-desc.svg");
              }
            }
          }

        }

        tbody {
          background-color: #11131D;
          tr {

            &.active {
              background-color: #212430;
            }

            td {
              border: 1px solid #363A47;
              font-size: 14px;
              line-height: 22px;
              padding: 16px 16px;

              &.left_9 {
                padding-left: 9px;
              }
            }

            &:hover {

            }

            &.active {

            }
            &.empty-data {
              text-align: center;

              td {
                border-bottom: none;
                height: 260px;
                vertical-align: middle;

                p {
                  margin: 0px;
                }
              }
            }
          }
        }
      }
    }
  }
</style>
