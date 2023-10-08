<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Vehicles Configure</div>
    </div>
    <div class="content">
      <div class="list">
        <div class="block">
          <vehicles ref="vehicles" />
        </div>
        <div class="block">
          <vehicle-types ref="vehicleTypes" @updated="onVehicleTypeUpdated" />
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .page {
    .nav {
      margin: 15px 15px 20px 15px;
      border-bottom: 1px solid #D7D7D7;
      .nav-link {
        color: #000;
        text-decoration: none;
        font-size: 18px;
        &.active {
          color: var(--md-theme-default-primary-on-background, #448aff);
          border-bottom: 3px solid var(--md-theme-default-primary-on-background, #448aff);
        }
      }
    }
    .content {
      width: 100%;
      .list {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        .block {
          margin: 15px;
          padding: 15px;
          background-color: white;
          box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
          position: relative;
          z-index: 1;
          width: 45%;
        }
      }
    }
  }
</style>
<script>
  import VehicleTypes from './VehicleTypes'
  import Vehicles from './Vehicles'

  export default {
    components: {
      VehicleTypes,
      Vehicles
    },

    methods: {
      onVehicleTypeUpdated (data) {
        this.$nextTick(() => {
          this.$refs.vehicles.getVehicleTypes()
          this.$refs.vehicles.updateVehicleType(data)
        })
      }
    }
  }
</script>
