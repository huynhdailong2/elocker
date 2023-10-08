import BaseModelRequest from './base/BaseModelRequest';

export default class VehicleRequest extends BaseModelRequest {

  getVehicles (params) {
    return this.get('/vehicles', params);
  }

  createVehicle (params) {
    return this.post('/vehicles/create', params);
  }

  updateVehicle (params) {
    return this.put('/vehicles/update', params);
  }

  revertVehicle (params) {
    return this.post('/vehicles/revert', params);
  }

  deleteVehicle (params) {
    return this.del('/vehicles/delete', params);
  }

  getVehicleTypes (params) {
    return this.get('/vehicle-types', params);
  }

  createVehicleType (params) {
    return this.post('/vehicle-types/create', params);
  }

  updateVehicleType (params) {
    return this.put('/vehicle-types/update', params);
  }

  deleteVehicleType (params) {
    return this.del('/vehicle-types/delete', params);
  }

  getVehicleStatistic (params) {
    return this.get('/vehicles/statistic', params);
  }

  getVehicleStatisticMonthly (params) {
    return this.get('/vehicles/statistic/monthly', params);
  }

  getDownloadExcelVehicles (params) {
    return this.download('/vehicles/export-excel-vehicles', params);
  }
}
