export default {
  USER_ROLE_SUPER_ADMIN: 1,
  USER_ROLE_ADMIN: 2,
  USER_ROLE_ADMIN_SUPPORT: 3,
  USER_ROLE_STOREMAN: 4,
  USER_ROLE_INSPECTOR: 5,
  USER_ROLES: [
    {value: 1, name: 'Super Admin'},
    {value: 2, name: 'Administrator'},
    {value: 3, name: 'Admin Support'},
    {value: 4, name: 'Storeman'},
    {value: 5, name: 'Inspector'},
  ],

  WEEKLY: [
    {name: 'monday', value: 1},
    {name: 'tuesday', value: 2},
    {name: 'wednesday', value: 3},
    {name: 'thursday', value: 4},
    {name: 'friday', value: 5},
    {name: 'saturday', value: 6},
    {name: 'sunday', value: 0},
  ],

  SPARES_REQUEST_STATUS_UNASSIGN        : 0,
  SPARES_REQUEST_STATUS_OVERDUE_AWS     : 1,
  SPARES_REQUEST_STATUS_OVERDUE_AWL     : 2,
  SPARES_REQUEST_STATUS_PENDING_FULFILL : 3,
  SPARES_REQUEST_STATUS_SPARE_FULFILL   : 4,
  SPARES_REQUEST_STATUS_TTC             : 5,
  SPARES_REQUEST_STATUS_ISSUED          : 6,
  SPARES_REQUEST_STATUS_PENDING_YT3     : 7,
  SPARES_REQUEST_STATUS_PENDING_YT5     : 8,

  VEHICLE_STATUS: [
      { 'value': 1, 'shortName': 'S', 'fullname': 'Serviceable'},
      { 'value': 2, 'shortName': 'US', 'fullname': 'Unserviceable'}
  ],

  CHECKLIST: [
    { value: 1, name: 'Remove P-Tape & P-Film from the air intake and Exhaust outlet ' },
    { value: 2, name: 'Install fuel strainer' },
    { value: 3, name: 'Check engine oil level' },
    { value: 4, name: 'Check radiator coolant level' },
    { value: 5, name: 'Check power steering reservoir oil level' },
    { value: 6, name: 'Check clutch fluid level' },
    { value: 7, name: 'Check brake fluid level' },
    { value: 8, name: 'Check front & rear axle oil level' },
    { value: 9, name: 'Check transimission Oil level (for IVECO only)' },
    { value: 10, name: 'Check transfer gearcase oil level' },
    { value: 11, name: 'Check front & rear wheel hub oil level' },
    { value: 12, name: 'Start Engine' },
    { value: 13, name: 'Check lightings and horn' },
    { value: 14, name: 'Check for sign of leakage' }
  ],

  CHECKLIST_ITEM_STATUS_PASSED  : 1, // Passed
  CHECKLIST_ITEM_STATUS_FAILED  : 0, // Failed
  CHECKLIST_ITEM_STATUS_NA      : -1, // N/A

  SHELF_TYPE: {
    MAIN    : { value: 'main', name: 'Main Cabinet' },
    SUB     : { value: 'sub', name: 'Sub Cabinet' }
  },

  // ======NEW
  ITEM_TYPE: {
    CONSUMABLE : { value: 'consumable', name: 'Consumable' },
    DURABLE    : { value: 'durable', name: 'STEs' }, // issue: Change durable to STEs
    PERISHABLE : { value: 'perishable', name: 'Perishable' },
    AFES       : { value: 'afes', name: 'AFES' },
    EUC        : { value: 'euc', name: 'EUC' },
    TORQUE_WRENCH : { value: 'torque_wrench', name: 'Torque wrench' },
    LIFTING_EQUIPMENT: { value: 'lifting_equipment', name: 'Lifting equipment' },
    OTHERS     : { value: 'others', name: 'Others' },
  },

  ITEM_STATE: {
    ALL_SPARES: 'all',
    ASSIGNED_BIN: 'assigned-bin',
    UNASSIGNED_BIN: 'unassigned-bin'
  },

  REPLENISH_TYPE: {
    MANUAL : { value: 'manual', name: 'Manual' },
    AUTO   : { value: 'auto', name: 'Auto' },
  },

  RECEIVER_EMAIL_TYPE: {
    CYCLE_COUNT       : 'cycle_count',
    INVENTORY_COUNT   : 'inventory_count',
    MAINTENANCE       : 'maintenance',
    VEHICLE           : 'vehicle',
    ALERT_WEIGHING_SYSTEM: 'alert_weighing_system',
    DEFAULT           : 'default'
  },

  DATETIME_PATTERN: 'YYYY-MM-DD HH:mm',
  DATE_PATTERN: 'YYYY-MM-DD',

  CLIENT_DATE_PATTERN: 'DD-MM-YYYY',
  CLIENT_DATETIME_PATTERN: 'DD-MM-YYYY HH:mm',

  POL_TYPE: {
    OIL           : { value: 'oil', name: 'Oil' },
    GREASE        : { value: 'grease', name: 'Grease' },
    COOLANT       : { value: 'coolant', name: 'Coolant' },
    APPLICATION   : { value: 'application', name: 'Application' },
    OTHERS        : { value: 'others', name: 'Others' },
  },

  VEICHLE_VARIANT_TYPE: {
    AGL  : { value: 'agl', name: 'AGL' },
    HMG  : { value: 'hmg', name: 'HMG' },
    CMG  : { value: 'cmg', name: 'CMG' },
  },

  VEICHLE_UNITS: {
    ADF     : { value: 'adf', name: 'ADF' },
    SOTF    : { value: 'sotf', name: 'SOTF' },
    SHW     : { value: 'shw', name: 'SHW' },
    OTHERS  : { value: 'others', name: 'Others' },
  },

  VEHICLE_STATUS: {
    OPENED        : { value: 'opened', name: 'Opened' },
    COMPLETING    : { value: 'completing', name: 'Completing' },
    COMPLETED     : { value: 'completed', name: 'Completed' }
  },

  POL_ACTION: {
    ISSUE: 'issue',
    REPLENISH: 'replenish'
  },

  KEY_RANGE_REPORT_TNX: 'range_get_report_tnx',
}
