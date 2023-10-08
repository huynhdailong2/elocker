<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Clusters / Shelfs / Bins / Items Configure</div>
    </div>
    <div class="clearfix"></div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.CLUSTERS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.CLUSTERS">Clusters
      </a>

      <a class="nav-link"
        :class="{'active': selected === TABS.SHELFS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.SHELFS">Shelfs
      </a>

      <a class="nav-link"
        :class="{'active': selected === TABS.BINS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.BINS">Bins
      </a>

      <a class="nav-link"
        :class="{'active': selected === TABS.ITEMS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.ITEMS">Items
      </a>
    </nav>

    <div class="content">
      <template v-if="selected === TABS.CLUSTERS">
        <cluster-configure />
      </template>
      <template v-if="selected === TABS.SHELFS">
        <shelf-configure />
      </template>
      <template v-if="selected === TABS.BINS">
        <bin-configure />
      </template>
      <template v-if="selected === TABS.ITEMS">
        <item-configure />
      </template>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .block {
    width: 45%;
  }
}
</style>
<script>
  import ClusterConfigure from './ClusterConfigure'
  import ShelfConfigure from './ShelfConfigure'
  import BinConfigure from './BinConfigure'
  import ItemConfigure from './ItemConfigure'

  const TABS = {
    CLUSTERS: 'clusters',
    SHELFS: 'shelfs',
    BINS: 'bins',
    ITEMS: 'items'
  }

  export default {
    components: {
      ClusterConfigure,
      ShelfConfigure,
      BinConfigure,
      ItemConfigure
    },

    data () {
      return {
        TABS,
        selected: TABS.CLUSTERS
      }
    },

    created () {
      let tab = this.$route.query.tab
      if (tab === null || !Object.values(this.TABS).includes(tab)) {
        tab = this.TABS.CLUSTERS
      }
      this.selected = tab
    },

    watch: {
      selected (val, old) {
        if (val === old) return
        this.$router.replace({ query: { tab: val } }).catch(err => {})
      }
    },
  }
</script>
