<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Inventory Dashboard</div>
    </div>
    <div class="content">
      <div class="bin-note">
        <div class="item good">Good</div>
        <div class="item onloan">On-Loan</div>
        <div class="item expiring">Expiring in 30 days</div>
        <div class="item expired">Expired</div>
      </div>

      <data-table2 :getData="getBinsSummary"
          :limit="limit"
          :column="column"
          :widthTable="'100%'"
          msgEmptyData=''
          @DataTable:finish="onDatatableFinish"
          ref="datatable">
        <template slot="body" slot-scope="props">
          <tr v-if="props.index < maxRealRow">
            <td v-for="(value, idxCol) in column" v-if="(props.index * column + idxCol) < rows.length" :key="idxCol">
              <div class="bin" :class="[getBgClass(rows[props.index * column + idxCol])]">
                <template v-if="!rows[props.index * column + idxCol].isEmpty">
                  <div class="left">
                    <div class="text-ellipsis">
                      <span class="bold">Bin: </span>
                      {{ rows[props.index * column + idxCol].shelf_name }} - {{ rows[props.index * column + idxCol].row }}- {{ rows[props.index * column + idxCol].bin }}
                    </div>
                    <div class="text-ellipsis">
                      <span class="bold">P/N:</span>
                      {{ rows[props.index * column + idxCol].part_no }}</div>
                  </div>
                  <div class="right">
                    <div class="circle">
                      <span>{{ rows[props.index * column + idxCol].percent }}%</span>
                    </div>
                  </div>
                </template>
                <template v-else>Empty</template>
              </div>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.page {
  .content {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    .bin-note {
      width: 100%;
      display: flex;
      justify-content: flex-end;
      margin: 20px 0;
      .item {
        padding: 5px 10px;
        // border: 1px solid #D7D7D7;
        & + .item {
          border-left: none;
        }
      }
    }
    .bin {
      min-width: 230px;
      height: 84px;
      padding: 12px 12px;
      background: #ECECEC;
      box-sizing: border-box;
      border-radius: 8px;
      box-shadow: 0px 4px 8px rgb(0 0 0 / 25%);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-left: 15px;
      margin-bottom: 15px;
      color: #fff;
      .left {
        float: left;
        div {
          &:first-child {
            margin-bottom: 15px;
          }
        }
      }
      .right {
        float: right;
        .circle {
          height: 60px;
          width: 60px;
          background-color: #fff;
          vertical-align: middle;
          border: 2px solid #ada2a2;
          border-radius: 50%;
          padding: 15px 0;
          text-align: center;
          span {
            color: #000;
            font-weight: 600;
          }
        }
      }
      &.empty {
        font-weight: bold;
        font-size: 16px;
        line-height: 20px;
        box-shadow: none;
        color: #D7D7D7;
        border: 1px dashed #404145;
      }
    }
    .bold {
      font-weight: 600;
    }
    .clearfix {
      content: "";
      clear: both;
      display: table;
    }
    .text-ellipsis {
      max-width: 130px;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .good {
      // background-color: #92D050;
      background: linear-gradient(270deg, #669827 0%, #385810 100%);
    }
    .onloan {
      // background-color: #FFFD27;
      background: linear-gradient(270deg, #E1A713 0%, #9B7513 100%);
    }
    .expiring {
      // background-color: #FFC011;
      background: linear-gradient(270deg, #E66714 0%, #9B3818 100%);
    }
    .expired {
      // background-color: #7A3D00;
      background: linear-gradient(270deg, #FF3131 0%, #861313 100%);
    }
    ::v-deep.box_table {
      background-color: initial;
      border: none;
      table {
        tr {
          td {
            padding: 0;
            border: none;
          }
        }
      }
    }
    ::v-deep.VuePagination {
      border: none;
      border-bottom: 1px solid #363A47;
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, size, filter } from 'lodash'

export default {
  components: {
  },

  data () {
    return {
      interval: null,

      column: 5,
      limit: 35,
      maxRealRow: 0,
      rows: []
    }
  },

  mounted () {
    this.spares = Array(20).fill(0)
    this.fetchData()

    this.interval = setInterval(() => {
      this.fetchData()
    }, 3000)
  },

  beforeDestroy () {
    clearInterval(this.interval)
  },

  methods: {
    fetchData () {
      this.$refs.datatable.refreshCurrentPage()
    },

    getBinsSummary (params) {
      return rf.getRequest('AdminRequest').getBinsSummary(params)
    },

    onDatatableFinish() {
      const refDatatable = this.$refs.datatable
      this.rows = refDatatable.rows

      const sizeBlank = this.limit - size(this.rows)

      let index = 0;
      while (index < sizeBlank) {
        this.rows.push({ isEmpty: true });
        index++;
      }

      this.maxRealRow = Math.ceil(this.rows.length / this.column)

      this.normalize()
    },

    normalize () {
      chain(this.rows)
        .filter(record => !record.isEmpty)
        .each(record => {
          const maxQty = record.max || 1
          const percent = (record.quantity_oh / maxQty * 100).toFixed(1)
          this.$set(record, 'percent', percent >= 100 ? 100 : percent)
        })
        .value()
    },

    getBgClass(item) {
      if (item.isEmpty) {
        return 'empty'
      }

      if (item.is_expired) {
        return 'expired'
      }

      if (item.is_onloan) {
        return 'onloan'
      }

      if (item.is_expiring_in_30days) {
        return 'expiring'
      }

      return 'good'
    },
  }
}
</script>
