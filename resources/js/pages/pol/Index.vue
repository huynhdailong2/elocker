<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">POL </div>
    </div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.ISSUE}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.ISSUE">Issue POL
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.MANAGEMENT}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.MANAGEMENT">POL Management
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.REPLENISH}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.REPLENISH">Replenish POL
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.HISTORY}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.HISTORY">POL Trans Log
      </a>
    </nav>
    <div class="content">
      <div class="list">
        <div class="block content-nav">
          <template v-if="selected === TABS.ISSUE">
            <issue-pol />
          </template>

          <template v-if="selected === TABS.MANAGEMENT">
            <pol-management />
          </template>

          <template v-if="selected === TABS.REPLENISH">
            <replenish-pol />
          </template>

          <template v-if="selected === TABS.HISTORY">
            <pol-histories />
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
}
</style>
<script>
import IssuePol from './IssuePol'
import PolManagement from './PolManagement'
import ReplenishPol from './ReplenishPol'
import PolHistories from './PolHistories'

const TABS = {
  ISSUE: 'issue',
  MANAGEMENT: 'management',
  REPLENISH: 'replenish',
  HISTORY: 'history'
}

export default {
  components: {
    IssuePol,
    PolManagement,
    ReplenishPol,
    PolHistories
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
      tab = this.TABS.ISSUE
    }
    this.selected = tab
  },

  watch: {
    selected (val, old) {
      if (val === old) return
      this.$router.replace({ query: { tab: val } }).catch(err => {})
    }
  }
}
</script>
