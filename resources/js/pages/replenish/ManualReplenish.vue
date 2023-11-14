<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Manual Replenish</div>
    </div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.OTHER}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.OTHER">Consumables - Perishable - AFES

      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.EUC}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.EUC">EUC
      </a>
    </nav>

    <div class="content">
        <div class="list">
          <div class="block">
            <template v-if="selected === TABS.OTHER">
              <index-manual />
            </template>
            <template v-if="selected === TABS.EUC">
              <replenish-euc />
            </template>
          </div>
        </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
</style>
<script>
import IndexManual from './partials/IndexManual'
import ReplenishEuc from './partials/ReplenishEuc'

const TABS = {
  EUC: 'euc',
  OTHER: 'other'
}

export default {
  components: {
    IndexManual,
    ReplenishEuc
  },

  data () {
    return {
      TABS,
      selected: TABS.OTHER
    }
  }
  }
</script>
