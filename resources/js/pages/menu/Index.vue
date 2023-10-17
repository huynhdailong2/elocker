<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">{{ selected | upperFirst }}</div>
    </div>

    <div class="menu-container">
      <div class="menu">
        <div class="menu-item"
          v-for="item in activeMenu"
          @click.stop="gotoPath(item.url)"
          v-if="item.visible" >
          <img :src="item.icon">
          <div>{{ item.name }}</div>
        </div>
      </div>
    </div>

  </div>
</template>
<style lang="scss" scoped>
$heightHeader: 80px;
$heightFooter: 50px;
$heightTitlePage: 100px;

.page {
  position: relative;
  height: calc(100vh - #{$heightHeader} - #{$heightFooter});
  .title-page {
    height: 100px;
    // padding-top: 40px;
    font-weight: bold;
    font-size: 40px;
    margin-bottom: 90px;
  }
  .menu-container {
    // height: calc(100% - #{$heightTitlePage});
    display: flex;
    align-items: center;
    justify-content: center;
    .menu {
      max-width: 850px;
      min-width: 800px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      .menu-item {
        min-width: 350px;
        margin: 15px;
        padding: 25px;
        background-color: #212430;
        color: #fff;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
        position: relative;
        z-index: 1;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        cursor: pointer;
        img {
          width: 80px;
          margin-bottom: 20px;
        }
      }
    }
  }
  .footer-page {
    position: absolute;
    bottom: 10px;
    text-align: center;
    width: 100%;
    .icon {
      display: inline-block;
      cursor: pointer;
      margin: 0 10px;
      img {
        width: 60px;
        margin-bottom: 20px;
        &.back {
          width: 40px;
        }
      }
    }
  }
}
</style>
<script>
import { chain, isEmpty } from 'lodash';
import { mapState } from 'vuex'
import Const from 'common/Const'

const MENU = {
  MAIN          : 'main',
  INVENTORY     : 'inventory',
  VEHICLES      : 'vehicles',
  RETURN        : 'return',
  REPLENISH     : 'replenish',
  PHYSICAL      : 'physical',
  MAINTENANCE   : 'maintenance',
  WEIGHING      : 'weighing',
}

export default {
  props: {
    selected: { type: String, default: MENU.MAIN }
  },

  computed: {
    ...mapState(['user']),
    activeMenu () {
      return this.getActiveMenu(this.menus())
    },

    isUserNormal () {
      return this.user.role === Const.USER_ROLE_INSPECTOR || this.user.role === Const.USER_ROLE_STOREMAN
    }
  },

  methods: {
    gotoPath (path) {
      this.$router.push(path)
    },

    getActiveMenu(menus = [], result = []) {
      const selectedMenu = this.selected || MENU.MAIN
      for (const menu of menus) {
        if (menu.type === selectedMenu) {
          result.push(menu)
        }
        this.getActiveMenu(menu.children, result)
      }
      return result
    },

    menus () {
      return [
        {
          name: 'Inventory Management',
          icon: '/images/icons/inventory-mgmt.svg',
          url: '/inventory',
          type: MENU.MAIN,
          visible: true,
          children: [
            {
              name: 'Dashboard',
              icon: '/images/icons/icon-dashboard.svg',
              url: '/inventory/dashboard',
              type: MENU.INVENTORY,
              visible: true,
            },
            {
              name: 'Issue',
              icon: '/images/icons/icon-issue.svg',
              url: '/inventory/issue',
              type: MENU.INVENTORY,
              visible: true,
            },
            {
              name: 'Return',
              icon: '/images/icons/icon-return.svg',
              url: '/inventory/return',
              type: MENU.INVENTORY,
              visible: true,
              // children: [
              //   {
              //     name: 'Return to Locker',
              //     icon: '/images/icons/icon-return-locker.svg',
              //     url: '/inventory/return/locker',
              //     type: MENU.RETURN
              //   },
              //   {
              //     name: 'Hand Over',
              //     icon: '/images/icons/icon-hand-over.svg',
              //     url: '/inventory/return/hand-over',
              //     type: MENU.RETURN
              //   }
              // ]
            },
            {
              name: 'Replenish',
              icon: '/images/icons/icon-replenish.svg',
              url: '/inventory/replenish',
              type: MENU.INVENTORY,
              visible: !this.isUserNormal,
              children: [
                {
                  name: 'Manual Replenish',
                  icon: '/images/icons/icon-manual-replenish.svg',
                  url: '/inventory/replenish/manual',
                  type: MENU.REPLENISH,
                  visible: true,
                },
                {
                  name: 'Auto Fill',
                  icon: '/images/icons/icon-auto-replenish.svg',
                  url: '/inventory/replenish/auto',
                  type: MENU.REPLENISH,
                  visible: true
                }
              ]
            },
            {
              name: 'Physical',
              icon: '/images/icons/icon-physical.svg',
              url: '/inventory/physical',
              type: MENU.INVENTORY,
              visible: !this.isUserNormal,
              children: [
                {
                  name: 'Cycle Count',
                  icon: '/images/icons/icon-cycle-count.svg',
                  url: '/inventory/physical/cycle-count',
                  type: MENU.PHYSICAL,
                  visible: true,
                },
                {
                  name: 'Inventory Count',
                  icon: '/images/icons/icon-inventory-count.svg',
                  url: '/inventory/physical/inventory-count',
                  type: MENU.PHYSICAL,
                  visible: true,
                },
                {
                  name: 'Edit EUC Box',
                  icon: '/images/icons/icon-euc-box.svg',
                  url: '/inventory/physical/euc-box',
                  type: MENU.PHYSICAL,
                  visible: true,
                }
              ]
            },
            {
              name: 'Maintenance',
              icon: '/images/icons/icon-maintenance.svg',
              url: '/inventory/maintenance',
              type: MENU.INVENTORY,
              visible: !this.isUserNormal,
              children: [
                {
                  name: 'Shelfs / Bins / Items Configure',
                  icon: '/images/icons/icon-shelf.svg',
                  url: '/inventory/maintenance/shelf-configure',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'Service/WO',
                  icon: '/images/icons/icon-job-card.svg',
                  url: '/inventory/maintenance/job-cards',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'Users',
                  icon: '/images/icons/icon-user.svg',
                  url: '/inventory/maintenance/users',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'Notifications',
                  icon: '/images/icons/icon-notification.svg',
                  url: '/inventory/maintenance/notifications',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'POL Management',
                  icon: '/images/icons/icon-manual-replenish.svg',
                  url: '/inventory/maintenance/pols',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'Transactions',
                  icon: '/images/icons/icon-report.svg',
                  url: '/inventory/maintenance/report',
                  type: MENU.MAINTENANCE,
                  visible: true,
                },
                {
                  name: 'EUC Transactions',
                  icon: '/images/icons/icon-report.svg',
                  url: '/inventory/replenish/euc-transaction',
                  type: MENU.MAINTENANCE,
                  visible: true
                },
                {
                  name: 'Area Used',
                  icon: '/images/icons/inventory-mgmt.svg',
                  url: '/inventory/maintenance/area-used',
                  type: MENU.MAINTENANCE,
                  visible: true
                }
              ]
            }
          ]
        },
        {
          name: 'Vehicle Management',
          icon: '/images/icons/vehicle-mgmt.svg',
          url: '/vehicles',
          type: MENU.MAIN,
          visible: true,
          children: [
            {
              name: 'Dashboard',
              icon: '/images/icons/icon-dashboard.svg',
              url: '/vehicles/dashboard',
              type: MENU.VEHICLES,
              visible: true,
            },
            {
              name: 'Vehicle Details',
              icon: '/images/icons/vehicle-mgmt.svg',
              url: '/vehicles/configure',
              type: MENU.VEHICLES,
              visible: true,
            },
            {
              name: 'Vehicle Scheduling',
              icon: '/images/icons/icon-vehicle-schedule.svg',
              url: '/vehicles/scheduling',
              type: MENU.VEHICLES,
              visible: true,
            },
            {
              name: 'Notifications',
              icon: '/images/icons/icon-notification.svg',
              url: '/vehicles/notifications',
              type: MENU.VEHICLES,
              visible: true
            }
          ]
        },
        // {
        //   name: 'Weighing System',
        //   icon: '/images/icons/weighing-system.svg',
        //   url: '/weighing',
        //   type: MENU.MAIN,
        //   visible: true,
        //   children: [
        //     {
        //       name: 'Bin Management',
        //       icon: '/images/icons/icon-bin-management.svg',
        //       url: '/weighing/bin-management',
        //       type: MENU.WEIGHING,
        //       visible: true,
        //     }
        //   ]
        // }
      ]
    }
  }
}
</script>
