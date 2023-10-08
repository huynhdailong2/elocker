<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Transactions</div>
    </div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.MASTER_LIST}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.MASTER_LIST">Inventory Master List
      </a>
      <!-- <a class="nav-link"
        :class="{'active': selected === TABS.WO_LISTING}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.WO_LISTING">WO Listing
      </a> -->
      <a class="nav-link"
        :class="{'active': selected === TABS.TNX_LISTING}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.TNX_LISTING">Trans Listing
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.LOAN}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.LOAN">On Loan
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.RETURN}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.RETURN">Return History
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.WRITE_OFF}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.WRITE_OFF">Write Off
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.TORQUE_WRENCH}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.TORQUE_WRENCH">Recall MME
      </a>
      <a class="nav-link"
         :class="{'active': selected === TABS.WEIGHING_HISTORY}"
         href="javascript:void(0)"
         @click.stop="selected = TABS.WEIGHING_HISTORY">Weighing System Transaction
      </a>
    </nav>
    <div class="content">
      <div class="list">
        <div class="block content-nav">
          <!-- <keep-alive> -->
          <template v-if="selected === TABS.MASTER_LIST">
            <InventoryMasterList />
          </template>

          <template v-if="selected === TABS.WO_LISTING">
            <WOListing />
          </template>

          <template v-if="selected === TABS.TNX_LISTING">
            <TnxListing />
          </template>

          <template v-if="selected === TABS.LOAN">
            <Loan />
          </template>

          <template v-if="selected === TABS.RETURN">
            <Return />
          </template>

          <template v-if="selected === TABS.WRITE_OFF">
            <write-off />
          </template>

          <template v-if="selected === TABS.TORQUE_WRENCH">
            <torque-wrenches />
          </template>

          <template v-if="selected === TABS.WEIGHING_HISTORY">
            <WeighingHistory />
          </template>
          <!-- </keep-alive> -->
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .schedule-container {
    border: 3px dotted #d3d3d3;
    border-radius: 10px;
    padding: 5px;
  }
}
</style>
<script>

import InventoryMasterList from './partial/inventory-master-list/InventoryMasterList'
import WOListing from './partial/wo-listing/WOListing'
import TnxListing from './partial/tnx-listing/TnxListing'
import Loan from './partial/loan/Loan'
import Return from './partial/return/Return'
import WriteOff from './partial/write-off/WriteOff'
import TorqueWrenches from './partial/torque-wrench/TorqueWrenches'
import WeighingHistory from "./partial/weighing-history/WeighingHistory";

const TABS = {
  MASTER_LIST: 'master-list',
  WO_LISTING: 'wo-listing',
  TNX_LISTING: 'tnx-listing',
  EXPRIED: 'expired',
  LOAN: 'loan',
  RETURN: 'return',
  WRITE_OFF: 'write-off',
  TORQUE_WRENCH: 'torque-wrench',
  WEIGHING_HISTORY: 'weighing-history',
}

export default {
  components: {
    InventoryMasterList,
    WOListing,
    TnxListing,
    Loan,
    Return,
    WriteOff,
    TorqueWrenches ,
    WeighingHistory,
  },

  data () {
    return {
      TABS,
      selected: null
    }
  },

  created () {
    let tab = this.$route.query.tab
    if (tab === null || !Object.values(this.TABS).includes(tab)) {
      tab = this.TABS.MASTER_LIST
    }
    this.selected = tab
  },

  watch: {
    selected (val, old) {
      if (val === old) return
      this.$router.replace({ query: { tab: val } }).catch(err => {})
    }
  },

  methods: {}
}
</script>
