<template>
  <div class="inventory-master-list">
    <div class="text-center">
      <h3>Inventory Master List</h3>
    </div>
    <nav class="nav nav-child">
<!--      <a class="nav-link"-->
<!--        :class="{'active': selected === TABS.EXPRITING}"-->
<!--        href="javascript:void(0)"-->
<!--        @click.stop="selected = TABS.EXPRITING">Expiring Items-->
<!--      </a>-->
<!--      <a class="nav-link"-->
<!--        :class="{'active': selected === TABS.YETTO}"-->
<!--        href="javascript:void(0)"-->
<!--        @click.stop="selected = TABS.YETTO">On Loan-->
<!--      </a>-->
<!--       <a class="nav-link"
        :class="{'active': selected === TABS.REORDER}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.REORDER">Re-Order Items
      </a> -->
    </nav>
    <div>
      <div class="list">
        <div class="block content-nav">
          <!-- <keep-alive> -->
            <template v-if="selected === TABS.YETTO">
              <YetToReturnItem />
            </template>
            <template v-if="selected === TABS.EXPRITING">
              <ExpiringItems />
            </template>
          <!-- </keep-alive> -->
<!--           <template v-if="selected === TABS.REORDER">
            124124
          </template> -->
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.inventory-master-list {
  .nav-child {
    border-bottom: none;
    .nav-link {
      margin-right: 4px;
      border: 1px solid #363A47;
      border-radius: 4px;
      // background-color: #aeabab;
      &.last-chid {
        margin-right: 0;
      }
      &.active {
        background-color: #b3c6e7;
      }
    }
  }
}
</style>
<script>
import ExpiringItems from './ExpiringItems'
import YetToReturnItem from './YetToReturnItem'
const TABS = {
  EXPRITING: 'expriting',
  YETTO: 'Yet To',
  REORDER: 'Re-order'
}

export default {
  components: {
    ExpiringItems,
    YetToReturnItem
  },

  data () {
    return {
      TABS,
      selected: TABS.EXPRITING
    }
  },

  methods: {}
}
</script>
