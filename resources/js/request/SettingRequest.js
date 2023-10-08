import BaseModelRequest from './base/BaseModelRequest';

export default class SettingRequest extends BaseModelRequest {

  getScheduleSettings(params) {
    return this.get('/settings/schedule', params);
  }

  saveSenderEmail(params) {
    return this.post('/settings/senders-email', params);
  }

  saveReceiverEmail(params) {
    return this.post('/settings/receivers-email', params);
  }

  saveCycleCountSchedule(params) {
    return this.post('/settings/cycle-schedule', params);
  }

  saveInventoryCountSchedule(params) {
    return this.post('/settings/inventory-schedule', params);
  }

  saveAlertWeighingSystemSchedule(params) {
    return this.post('/settings/alert-weighing-system-schedule', params);
  }

  saveByKey (params) {
    return this.post('/settings/save-by-key', params);
  }

  getByKey (params) {
    return this.get('/settings/get-by-key', params);
  }
}
