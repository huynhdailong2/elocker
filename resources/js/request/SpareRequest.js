import BaseModelRequest from './base/BaseModelRequest';

export default class SpareRequest extends BaseModelRequest {

  issueCard (params) {
    return this.post('/issue-card', params);
  }

  createLinkMO (params) {
    return this.post('/issue-card/create-link-mo', params);
  }

  updateLinkMO (params) {
    return this.put('/issue-card/update-link-mo', params);
  }

  deleteLinkMO (params) {
    return this.del('/issue-card/delete-link-mo', params);
  }

  // getIssueCardHistories (params) {
  //   return this.get('/issue-card/histories', params);
  // }

  replenishManual (params) {
    return this.post('/replenish/manual', params);
  }

  replenishManualForEuc (params) {
    return this.post('/replenish/manual-euc', params);
  }

  getReplenishAutoList (params) {
    return this.get('/replenish/auto', params);
  }

  getReplenishAutoByUuid (params) {
    return this.get('/replenish/auto/info', params);
  }

  deleteReplenishAuto (params) {
    return this.del('/replenish/auto/delete', params);
  }

  removeSpareByBinReplenishAuto (params) {
    return this.del('/replenish/auto/remove-spare-by-bin', params);
  }

  replenishAuto (params) {
    return this.post('/replenish/auto/create', params);
  }

  confirmReplenishAuto (params) {
    return this.put('/replenish/auto/confirm', params);
  }

  getEucItems (params) {
    return this.get('/euc-histories', params)
  }

  generateCycleCount (params) {
    return this.post('/cycle-count/generate', params)
  }

  getSparesReturn (params = null) {
    return this.get('/returns/spares', params);
  }

  returnToStore (params) {
    return this.put('/returns/store', params)
  }

  returnByHandOver (params) {
    return this.put('/returns/handover', params)
  }

  getSparesExpiring (params) {
    return this.get('/report/spares-expiring', params)
  }

  getYetToReturnSpares (params) {
    return this.get('/report/yet-return-spares', params)
  }

  getSparesReportByWo (params) {
    return this.get('/report/spares-wo', params)
  }

  sendSparesExpiringReport (params) {
    return this.post('/report/spares-expiring/by-mail', params)
  }

  sendYetToReturnSparesReport (params) {
    return this.post('/report/yet-return-spares/by-mail', params)
  }

  sendSparesReportByWo (params) {
    return this.post('/report/spares-wo/by-mail', params)
  }

  getSparesReportByTnx (params) {
    return this.get('/report/tnx', params)
  }

  getSparesReportByLoan (params) {
    return this.get('/report/loan', params)
  }

  getSparesReportByExpired (params) {
    return this.get('/report/expired', params)
  }

  getSparesReportForReturns (params) {
    return this.get('/report/returns', params)
  }

  sendSparesByTnxReport (params) {
    return this.post('/report/spares-tnx/by-mail', params)
  }

  sendSparesByReturnsReport (params) {
    return this.post('/report/spares-returns/by-mail', params)
  }

  sendSparesByExpiredReport (params) {
    return this.post('/report/spares-expired/by-mail', params)
  }

  sendSparesByLoanReport (params) {
    return this.post('/report/spares-loan/by-mail', params)
  }

  writeOffSpareExpired (params) {
    return this.post('/report/spares-expiring/write-off', params)
  }

  unwriteOffSpareExpired (params) {
    return this.post('/report/spares-expiring/unwrite-off', params)
  }

  getSparesWriteOff (params) {
    return this.get('/report/write-off', params)
  }

  sendSparesWriteOffReport (params) {
    return this.post('/report/write-off/by-mail', params)
  }

  getSparesTorqueWrench (params) {
    return this.get('/report/torque-wrench', params)
  }

  sendSparesTorqueWrenchReport (params) {
    return this.post('/report/torque-wrench/by-mail', params)
  }

  sendTnxReportNotification () {
    return this.post('/report/spares-tnx-notification/by-mail')
  }
}
