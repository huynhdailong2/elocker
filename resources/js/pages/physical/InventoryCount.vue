<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Physical Inventory Count</div>
    </div>

    <div class="block">
      <div class="filter">
        <span class="label">Item Type: </span>
        <select class="options" v-model="selectedType" >
          <option value="">All</option>
          <option :value="item.value" v-for="(item, index) in types" :key="index">
            {{ item.name }}
          </option>
        </select>
      </div>
      <div class="table-scroller">
        <table>
          <thead>
            <th>S/N</th>
            <th>MPN</th>
            <th>SSN</th>
            <th>Description</th>
            <th>Location</th>
            <th>Item Type</th>
            <th>Qty OH</th>
          </thead>
          <tbody>
            <template v-if="noData">
              <tr>
                <td colspan="6">There is no data.</td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="(item, index) in spares">
                <td><div>{{ index + 1 }}</div></td>
                <td><div>{{ item.material_no }}</div></td>
                <td><div>{{ item.part_no }}</div></td>
                <td><div>{{ item.name }}</div></td>
                <td><div>{{ item.type == Const.ITEM_TYPE.EUC.value && item.euc_box_order ? `EUC #${item.euc_box_order}` : item.cluster_name + ' - ' + item.shelf_name + ' - ' + item.row + ' - ' + item.bin }}</div></td>
                <td><div class="text-capitalize">{{ getLabelByType(item.type) }}</div></td>
                <td><div>{{ item.quantity_oh || 0 }}</div></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div class="print">
        <div class="lalbel">Print Now:</div>
        <div class="btn btn-primary" @click.stop="onClickPrint(false)">With OH Qty</div>
        <div class="btn btn-primary" @click.stop="onClickPrint(true)">Without OH Qty</div>
      </div>

      <div class="schedule">
        <schedule ref="schedule" :mail-type="mailType" hide-sender />
      </div>
    </div>

    <inventory-count-print
      :data="spares"
      :config="config"
      ref="inventoryCountPrinter" />

  </div>
</template>
<style lang="scss" scoped>
  .page {
    margin: auto;
    width: 70%;
    .filter {
      display: flex;
      margin-bottom: 20px;
      .label {
        margin-right: 20px;
        line-height: 40px;
      }
      select {
        width: 150px;
      }
    }
    .print {
      margin-top: 30px;
      display: flex;
      justify-content: center;
      align-content: center;
      .lalbel {
        font-size: 16px;
        font-weight: bold;
        line-height: 65px;
        margin-right: 30px;
      }
      .btn {
        margin: 10px;
      }
    }
    .schedule {
      padding: 30px 20px;
      display: flex;
      justify-content: center;
      align-content: center;
      border: dotted #989090;
      border-radius: 30px;
    }
  }
</style>
<script>
import rf from 'requestfactory'
import {chain, debounce, isEmpty} from 'lodash'
import Const from 'common/Const'
import Schedule from './partials/Schedule'
import InventoryCountPrint from './partials/InventoryCountPrint'

export default {
  components: {
    Schedule,
    InventoryCountPrint
  },

  data () {
    return {
      config: {
        withoutQty: false
      },
      spares: [],
      selectedType: '',
      Const
    }
  },

  computed: {
    mailType () {
      return Const.RECEIVER_EMAIL_TYPE.INVENTORY_COUNT
    },

    types () {
      return Object.values(Const.ITEM_TYPE)
    },

    noData () {
      return isEmpty(this.spares)
    }
  },

  watch: {
    selectedType: debounce(function (newVal) {
        this.getSpares()
      }, 400),
  },

  mounted () {
    this.getSpares()
  },

  methods: {
    getSpares () {
      const params = {
        no_pagination: true,
        type: isEmpty(this.selectedType) ? null : this.selectedType
      }
      rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        .then(res => {
          this.spares = res.data
        })
    },

    onClickPrint (withoutQty = false) {
      this.config.withoutQty = withoutQty

      this.$nextTick(() => {
        this.$refs.inventoryCountPrinter.print();
      })
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
