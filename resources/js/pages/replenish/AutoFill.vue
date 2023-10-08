<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Auto Fill</div>
    </div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.INPUT_RL}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.INPUT_RL">Input RL

      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.NEW_REQUEST}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.NEW_REQUEST">Replenishment List
      </a>
    </nav>

    <div class="content">
        <div class="list">
          <div class="block" :class="{'input-rl': selected === TABS.INPUT_RL}">
            <template v-if="selected === TABS.INPUT_RL">
              <replenish-scanning-auto />
            </template>
            <template v-if="selected === TABS.NEW_REQUEST">
              <replenishments />
            </template>
          </div>
        </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.page {
  .content {
    .list {
      .input-rl {
        min-height: auto;
      }
    }
  }
}
</style>
<script>
import ReplenishScanningAuto from './partials/ReplenishScanningAuto'
import Replenishments from './partials/Replenishments'

const TABS = {
  INPUT_RL: 'input-rl',
  NEW_REQUEST: 'new-request'
}

export default {
  components: {
    ReplenishScanningAuto,
    Replenishments
  },

  data () {
    return {
      TABS,
      selected: TABS.INPUT_RL
    }
  }
  }
</script>
