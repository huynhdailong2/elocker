<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Vehicles Configure</div>
    </div>
    <nav class="nav">
      <a class="nav-link"
        :class="{'active': selected === TABS.VEHICLE_DETAILS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.VEHICLE_DETAILS">Vehicle Details
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.COMPLETION_JOBS}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.COMPLETION_JOBS">Completion Jobs
      </a>
      <a class="nav-link"
        :class="{'active': selected === TABS.VEHICLE_TYPES}"
        href="javascript:void(0)"
        @click.stop="selected = TABS.VEHICLE_TYPES">Vehicle Types
      </a>
    </nav>
    <div class="content">
      <div class="list">
        <div class="block content-nav">
          <template v-if="selected === TABS.VEHICLE_DETAILS">
            <vehicle-details />
          </template>

          <template v-if="selected === TABS.COMPLETION_JOBS">
            <vehicle-completion />
          </template>

          <template v-if="selected === TABS.VEHICLE_TYPES">
            <vehicle-types />
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
import VehicleTypes from './VehicleTypes'
import VehicleDetails from './VehicleDetails'
import VehicleCompletion from './VehicleCompletion'

const TABS = {
  VEHICLE_DETAILS: 'details',
  COMPLETION_JOBS: 'completion-jobs',
  VEHICLE_TYPES: 'types'
}

export default {
  components: {
    VehicleDetails,
    VehicleCompletion,
    VehicleTypes
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
      tab = this.TABS.VEHICLE_DETAILS
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
