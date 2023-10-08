
import Layout from './pages/Layout';
import WrapPage from './pages/WrapPage';

import Login from './pages/auth/Login';
import MenuIndex from './pages/menu/Index';

import InventoryDashboard from './pages/inventory-dashboard/Index';
import IssueIndex from './pages/issue/Index';

import VehicleDashboard from './pages/vehicles/dashboard/Index';
import VehicleIndex from './pages/vehicles/details/Index';
import VehicleScheduling from './pages/vehicles/scheduling/VehicleScheduling';
import VehicleNotifications from './pages/vehicles/notifications/Notifications';

import ShelfConfigureIndex from './pages/maintenance/Index';
import Notifications from './pages/maintenance/Notifications';
import MaintenanceReport from './pages/maintenance/MaintenanceReport';
import JobCards from './pages/job-card/JobCards';
import UserManagement from './pages/user/UserManagement';
import PolIndex from './pages/pol/Index';
import AreaUsed from './pages/maintenance/AreaUsed';

import CycleCount from './pages/physical/CycleCount';
import InventoryCount from './pages/physical/InventoryCount';
import EucBoxes from './pages/physical/EucBoxes';

import ManualReplenish from './pages/replenish/ManualReplenish';
import AutoFill from './pages/replenish/AutoFill';
import EucTransaction from './pages/replenish/EucTransaction'

import ReturnIndex from './pages/return/Index';

import NotFound from './pages/NotFound';
import Const from "./common/Const";

import WeighingBinManagement from './pages/weighing/bin-management/Index';

export default {
  mode: 'history',
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: Login,
      meta: {
        guest: true,
      },
    },
    {
      path: '/',
      component: Layout,
      redirect: '/home',
      children: [
        {
          path: 'home',
          name: 'Menu Index',
          component: MenuIndex,
          meta: {
            auth: true
          }
        },
        {
          path: '/inventory',
          name: 'Inventory WrapPage',
          component: WrapPage,
          meta: {
            auth: true
          },
          children: [
            {
              path: '',
              name: 'Inventory Menu',
              component: MenuIndex,
              props: {
                selected: 'inventory'
              }
            },
            {
              path: 'dashboard',
              name: 'Inventory Dashboard',
              component: InventoryDashboard
            },
            {
              path: 'issue',
              name: 'Issue WrapPage',
              component: WrapPage,
              children: [
                {
                  path: '',
                  name: 'Issue Index',
                  component: IssueIndex,
                }
              ]
            },
            {
              path: 'maintenance',
              name: 'Maintenance WrapPage',
              component: WrapPage,
              children: [
                {
                  path: '',
                  name: 'Maintenance Menu',
                  component: MenuIndex,
                  props: {
                    selected: 'maintenance'
                  },
                },
                {
                  path: 'shelf-configure',
                  name: 'Shelf Configure',
                  component: ShelfConfigureIndex,
                },
                {
                  path: 'job-cards',
                  name: 'Job Cards',
                  component: JobCards,
                },
                {
                  path: 'users',
                  name: 'User Management',
                  component: UserManagement,
                },
                {
                  path: 'notifications',
                  name: 'Notifications',
                  component: Notifications,
                },
                {
                  path: 'report',
                  name: 'Maintenance Report',
                  component: MaintenanceReport
                },
                {
                  path: 'pols',
                  name: 'Pol Index',
                  component: PolIndex
                },
                {
                  path: 'area-used',
                  name: 'Area Used',
                  component: AreaUsed
                }
              ]
            },
            {
              path: 'physical',
              name: 'Physical WrapPage',
              component: WrapPage,
              children: [
                {
                  path: '',
                  name: 'Physical Menu',
                  component: MenuIndex,
                  props: {
                    selected: 'physical'
                  },
                },
                {
                  path: 'cycle-count',
                  name: 'Cycle Count',
                  component: CycleCount,
                },
                {
                  path: 'inventory-count',
                  name: 'Inventory Count',
                  component: InventoryCount,
                },
                {
                  path: 'euc-box',
                  name: 'Edit Euc Box',
                  component: EucBoxes,
                }
              ]
            },
            {
              path: 'replenish',
              name: 'Replensih WrapPage',
              component: WrapPage,
              children: [
                {
                  path: '',
                  name: 'Replensih Menu',
                  component: MenuIndex,
                  props: {
                    selected: 'replenish'
                  },
                },
                {
                  path: 'manual',
                  name: 'Manual Replenish',
                  component: ManualReplenish,
                },
                {
                  path: 'auto',
                  name: 'Auto Fill',
                  component: AutoFill,
                },
                {
                  path: 'euc-transaction',
                  name: 'Euc Transaction',
                  component: EucTransaction,
                }
              ]
            },
            {
              path: 'return',
              name: 'Return Index',
              component: ReturnIndex,
              meta: {
                auth: true
              },
              // children: [
              //   {
              //     path: '',
              //     name: 'Return Menu',
              //     component: MenuIndex,
              //     props: {
              //       selected: 'return'
              //     },
              //   },
              //   {
              //     path: 'locker',
              //     name: 'Return Locker',
              //     component: ReturnLocker,
              //   },
              //   {
              //     path: 'hand-over',
              //     name: 'Hand Over',
              //     component: HandOver,
              //   }
              // ]
            },
          ]
        },
        {
          path: '/vehicles',
          name: 'Vehicle WrapPage',
          component: WrapPage,
          meta: {
            auth: true
          },
          children: [
            {
              path: '',
              name: 'Vehicle Menu',
              component: MenuIndex,
              props: {
                selected: 'vehicles'
              }
            },
            {
              path: 'configure',
              name: 'Vehicles',
              component: VehicleIndex,
            },
            {
              path: 'scheduling',
              name: 'Vehicle Scheduling',
              component: VehicleScheduling,
            },
            {
              path: 'notifications',
              name: 'Vehicle Notifications',
              component: VehicleNotifications,
            },
            {
              path: 'dashboard',
              name: 'Vehicle Dashboard',
              component: VehicleDashboard,
            }
          ]
        },
        {
          path: '/weighing',
          name: 'Weighing WrapPage',
          component: WrapPage,
          meta: {
            auth: true
          },
          children: [
            {
              path: '',
              name: 'Inventory Menu',
              component: MenuIndex,
              props: {
                selected: 'weighing'
              }
            },
            {
              path: 'bin-management',
              name: 'Bin Management',
              component: WeighingBinManagement
            }
          ]
        },
        {
          path: '/404',
          component: NotFound
        }
      ]
    },
    {
      path: '*',
      redirect: '404'
    }
  ],
  urls_dined: [
    {
      "role_id": Const.USER_ROLE_INSPECTOR,
      "urls": [
        {
          "url": "/inventory/maintenance/users"
        }
      ]
    }
  ]
}
