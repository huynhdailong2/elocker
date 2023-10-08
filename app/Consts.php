<?php

namespace App;

class Consts
{
    const TRUE      = 1;
    const FALSE     = 0;

    const SORT_TYPE_ASC     = 'asc';
    const SORT_TYPE_DESC    = 'desc';

    const DEFAULT_LOCALE    = 'en';

    const DEFAULT_PER_PAGE  = 10;

    const CHAR_COMMA = ',';
    const CHAR_BACKTICKS = '`';
    const CHAR_DOUBLE_QUOTE = '"';
    const CHAR_SINGLE_QUOTE = "'";
    const CHAR_QUESTION_MARK = '?';
    const CHAR_SPAPCE = ' ';
    const STRING_EMPTY = '';
    const CHAR_UNDERSCORE = '_';

    const USER_ROLE_SUPER_ADMIN         = 1;
    const USER_ROLE_ADMINISTRATOR       = 2;
    const USER_ROLE_ADMIN_SUPPORT       = 3;
    const USER_ROLE_STOREMAN            = 4;
    const USER_ROLE_INSPECTOR           = 5;

    const SYSTEM_TYPE_LOG           = 'logging';
    const SYSTEM_TYPE_EXCEPTION     = 'exception';

    const ACTION_INSERT = 'insert';
    const ACTION_SELECT = 'select';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const DEFAULT_TIMESTAMP = '1719-06-09 00:00:00';

    const SCHEDULE_REPORT_WEEKLY    = 'weekly';
    const SCHEDULE_REPORT_MONTHLY   = 'monthly';

    const WEEKLY = [
        'monday'    => 1,
        'tuesday'   => 2,
        'wednesday' => 3,
        'thursday'  => 4,
        'friday'    => 5,
        'saturday'  => 6,
        'sunday'    => 0,
    ];

    const WEEKLY_KEY        = 'weekly_setting';
    const MONTHLY_KEY       = 'monthly_setting';

    const SITE_NAME_KEY         = 'site_name';
    const SENDER_EMAIL_KEY      = 'sender_email';
    const SENDER_PASSWORD_KEY   = 'sender_password';
    const SCHEDULE_TYPE_CYCLE_COUNT_KEY                 = 'schedule_cycle_count';
    const SCHEDULE_TYPE_INVENTORY_COUNT_KEY             = 'schedule_inventory_count';
    const SCHEDULE_TYPE_ALERT_WEIGHING_SYSTEM_KEY       = 'schedule_alert_weighing_system';
    const SCHEDULE_TYPE_MAINTENANCE_KEY                 = 'schedule_maintenance';

    const RECEIVER_EMAIL_TYPE_CYCLE_COUNT               = 'cycle_count';
    const RECEIVER_EMAIL_TYPE_INVENTORY_COUNT           = 'inventory_count';
    const RECEIVER_EMAIL_TYPE_MAINTENANCE               = 'maintenance';
    const RECEIVER_EMAIL_TYPE_VEHICLE                   = 'vehicle';
    const RECEIVER_EMAIL_TYPE_DEFAULT                   = 'default';
    const RECEIVER_EMAIL_TYPE_ALERT_WEIGHING_SYSTEM     = 'alert_weighing_system';

    const SHELF_TYPE_MAIN    = 'main';
    const SHELF_TYPE_SUB     = 'sub';

    const SPARE_TYPE_CONSUMABLE     = 'consumable';
    const SPARE_TYPE_DURABLE        = 'durable'; // issue: Change word: durable to STEs
    const SPARE_TYPE_DURABLE_2      = 'stes'; // the same with durable
    const SPARE_TYPE_PERISHABLE     = 'perishable';
    const SPARE_TYPE_AFES           = 'afes';
    const SPARE_TYPE_EUC            = 'euc';
    const SPARE_TYPE_TORQUE_WRENCH  = 'torque_wrench';
    const SPARE_TYPE_LIFTING_EQUIPMENT  = 'lifting_equipment';
    const SPARE_TYPE_OTHERS         = 'others';

    const REPLENISHMENT_TYPE_MANUAL = 'manual';
    const REPLENISHMENT_TYPE_AUTO   = 'auto';

    // for return module
    const RETURN_TO_STORE   = 'return_store';
    const HAND_OVER         = 'handover';

    const RETURNED_TYPE_ALL      = 'all';
    const RETURNED_TYPE_PARTIAL  = 'partial';
    const RETURNED_TYPE_LINK_MO  = 'mo';

    const RETURN_SPARE_STATE_WORKING    = 'working';
    const RETURN_SPARE_STATE_DAMAGE     = 'damage';
    const RETURN_SPARE_STATE_EXPIRED    = 'expired';
    const RETURN_SPARE_STATE_FINISHED   = 'finished';
    const RETURN_SPARE_STATE_INCOMPLETE = 'incomplete';

    const BIN_STATUS_UNASSIGNED = 'unassigned';
    const BIN_STATUS_ASSIGNED   = 'assigned';

    const POL_STATUS_NA             = 'n/a';
    const POL_STATUS_REQUESTED      = 'requested';
    const POL_STATUS_RECEIVING      = 'receiving';
    const POL_STATUS_RECEIVED       = 'received';
    const POL_STATUS_ISSUING        = 'issuing';
    const POL_STATUS_ISSUED         = 'issued';

    const POL_TYPE_OIL              = 'oil';
    const POL_TYPE_GREASE           = 'grease';
    const POL_TYPE_COOLANT          = 'coolant';
    const POL_TYPE_APPLICATION      = 'application';
    const POL_TYPE_OTHERS           = 'others';

    const VEHICLE_STATUS_OPENED         = 'opened'; // new job
    const VEHICLE_STATUS_COMPLETING     = 'completing'; // job completed but admin can revert to opened
    const VEHICLE_STATUS_COMPLETED      = 'completed'; // job completed, cannot change

    const POL_HISTORY_TYPE_ISSUE        = 'issue';
    const POL_HISTORY_TYPE_REPLENISH    = 'replenish';

    const TAKING_TRANSACTION_TYPE_ISSUE     = 'issue';
    const TAKING_TRANSACTION_TYPE_REPLENISH = 'replenish';
    const TAKING_TRANSACTION_TYPE_REPLENISH_AUTO = 'replenish-auto';
    const TAKING_TRANSACTION_TYPE_RETURN    = 'return';
    const TAKING_TRANSACTION_TYPE_WEIGHING_SYSTEM    = 'weighing-system';

    const TAKING_TRANSACTION_STATUS_OPENED    = 'opened';
    const TAKING_TRANSACTION_STATUS_COMPLETED = 'completed';

    const BIN_IS_FAILED_YES = 1;
    const BIN_IS_FAILED_NO = 0;
}
