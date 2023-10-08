<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Vehicle Dashboard</div>
    </div>
    <div class="content">
      <div class="cols">
        <div class="block">
          <div class="title">Serviceability Table</div>
          <serviceability-chart :data="vehicles"/>
        </div>
        <div class="block">
          <div class="title">Vehicle On Loan</div>
          <vehicle-loan-chart :data="vehicles" />
        </div>
        <div class="block">
          <div class="title">Vehicle Variant Status</div>
          <vehicle-variant-status-chart :data="vehicles" />
        </div>
      </div>
      <div class="cols">
        <div class="block">
          <div class="title">Servicing Schedule</div>
          <servicing-schedule-chart :data="vehiclesMonthly"/>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.page {
  .content {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    .cols {
      width: 90%;
      display: flex;
      margin-top: 15px;
      .block {
        border-radius: 9px;
        margin: 0;
        margin-top: 1em;
        margin-right: 1em;
        color: #fff;
        .title {
          font-size: 16px;
          font-weight: bold;
          text-align: center;
          margin-bottom: 40px;
        }
        .desc {
          font-size: 14px;
          color: #6D6E71;
          height: 40px;
        }
        .content-block {
          margin-top: 1em;
          position: relative;
        }
      }
      &.flexiable {
        width: 100%;
        flex: auto;
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import ServiceabilityChart from './partial/ServiceabilityChart'
import VehicleLoanChart from './partial/VehicleLoanChart'
import ServicingScheduleChart from './partial/ServicingScheduleChart'
import VehicleVariantStatusChart from './partial/VehicleVariantStatusChart'

export default {
  components: {
    ServiceabilityChart,
    VehicleLoanChart,
    ServicingScheduleChart,
    VehicleVariantStatusChart
  },

  data () {
    return {
      interval: null,
      vehicles: [],
      vehiclesMonthly: []
    }
  },

  mounted () {
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
      this.$nextTick(() => {
        this.getVehicleStatistic()
        this.getVehicleStatisticMonthly()
      })
    },

    async getVehicleStatistic () {
      const response = await rf.getRequest('VehicleRequest').getVehicleStatistic()
      this.vehicles = response.data || []
    },

    async getVehicleStatisticMonthly () {
      const response = await rf.getRequest('VehicleRequest').getVehicleStatisticMonthly()
      this.vehiclesMonthly = response.data || []
    }
  }
}
</script>
