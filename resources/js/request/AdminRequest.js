import BaseModelRequest from "./base/BaseModelRequest";

export default class AdminRequest extends BaseModelRequest {
  getClusters(params) {
    return this.get("/configure/clusters", params);
  }

  createCluster(params) {
    return this.post("/configure/clusters/create", params);
  }

  updateCluster(params) {
    return this.put("/configure/clusters/update", params);
  }

  deleteCluster(params) {
    return this.del("/configure/clusters/delete", params);
  }

  updateVirtualCluster(params) {
    return this.put("/configure/clusters/update-virtual", params);
  }

  getTorqueWrenchAreas(params) {
    return this.get("/torque-areas", params);
  }

  createTorqueWrenchArea(params) {
    return this.post("/torque-areas/create", params);
  }

  updateTorqueWrenchArea(params) {
    return this.put("/torque-areas/update", params);
  }

  deleteTorqueWrenchArea(params) {
    return this.del("/torque-areas/delete", params);
  }

  getShelfs(params) {
    return this.get("/configure/shelfs", params);
  }

  createShelf(params) {
    return this.post("/configure/shelfs/create", params);
  }

  updateShelf(params) {
    return this.put("/configure/shelfs/update", params);
  }

  deleteShelf(params) {
    return this.del("/configure/shelfs/delete", params);
  }

  getBins(params) {
    return this.get("/configure/bins", params);
  }
  getBinId(params) {
    return this.get(`/configure/bins/${params}`);
  }

  getBinsSummary(params) {
    return this.get("/configure/bins/dashboard", params);
  }

  updateBin(params) {
    return this.put("/configure/bins/update", params);
  }

  patchBin(params) {
    return this.put("/configure/bins/patch", params);
  }

  unassignedBin(params) {
    return this.put("/configure/bins/unassigned", params);
  }

  getSpares(params) {
    return this.get("/configure/spares", params);
  }

  getSpareByMpn(params) {
    return this.get("/configure/spares/by-mpn", params);
  }

  getSpareByPartNo(params) {
    return this.get("/configure/spares/by-pn", params);
  }

  getSparesUnassigned(params) {
    return this.get("/configure/spares/unassigned", params);
  }

  getItemsForIssuing(params) {
    return this.get("/configure/spares/issuing", params);
  }

  getSparesAssignedBin(params) {
    return this.get("/configure/spares/assigned-bin", params);
  }

  createSpare(params) {
    return this.post("/configure/spares/create", params);
  }

  updateSpare(params) {
    return this.post("/configure/spares/update", params);
  }

  deleteSpare(params) {
    return this.del("/configure/spares/delete", params);
  }

  importSpares(params) {
    return this.post("/configure/spares/import", params);
  }

  exportSpares() {
    return this.downloadExcel("/configure/spares/export", "spares");
  }

  getVehicles(params) {
    return this.get("/vehicles", params);
  }

  createVehicle(params) {
    return this.post("/vehicles/create", params);
  }

  updateVehicle(params) {
    return this.put("/vehicles/update", params);
  }

  deleteVehicle(params) {
    return this.del("/vehicles/delete", params);
  }

  getVehicleTypes(params) {
    return this.get("/vehicle-types", params);
  }

  createVehicleType(params) {
    return this.post("/vehicle-types/create", params);
  }

  updateVehicleType(params) {
    return this.put("/vehicle-types/update", params);
  }

  deleteVehicleType(params) {
    return this.del("/vehicle-types/delete", params);
  }

  getJobCards(params) {
    return this.get("/job-cards", params);
  }

  getClosedJobCards(params) {
    return this.get("/job-cards/closed-job-cards", params);
  }

  getJobCardByCardNumber(params) {
    return this.get("/job-cards/by-card-num", params);
  }

  createJobCard(params) {
    return this.post("/job-cards/create", params);
  }

  updateJobCard(params) {
    return this.put("/job-cards/update", params);
  }

  deleteJobCard(params) {
    return this.del("/job-cards/delete", params);
  }

  closedJobCard(params) {
    return this.put("/job-cards/closed", params);
  }

  scanBarcode(params) {
    return this.post("/job-cards/scan-barcode", params);
  }

  finishedScanBarcode() {
    return this.post("/job-cards/finished-scan-barcode");
  }

  getEucLists(params) {
    return this.get("/euc-lists", params);
  }

  createEuc(params) {
    return this.post("/euc-lists/create-uec", params);
  }

  createEucList(params) {
    return this.post("/euc-lists/create", params);
  }

  updateEuc(params) {
    return this.put("/euc-lists/update-uec", params);
  }

  updateEucList(params) {
    return this.put("/euc-lists/update", params);
  }

  updateItemsEuc(eucBoxId, params) {
    return this.put("/euc-lists/update-items-euc/" + eucBoxId, params);
  }

  deleteEucList(params) {
    return this.del("/euc-lists/delete", params);
  }

  getTorqueAreas(params) {
    return this.get("/torque-areas", params);
  }

  getVehicleSchedulings(params) {
    return this.get("/vehicle-schedulings", params);
  }

  createVehicleScheduling(params) {
    return this.post("/vehicle-schedulings/create", params);
  }

  updateVehicleScheduling(params) {
    return this.put("/vehicle-schedulings/update", params);
  }

  deleteVehicleScheduling(params) {
    return this.del("/vehicle-schedulings/delete", params);
  }

  getPolManagements(data) {
    return this.get("/pol-management", data);
  }

  getPolHistories(data) {
    return this.get("/pol-management/histories", data);
  }

  getPolManagementInfo(data) {
    return this.get(`/pol-management/info`, data);
  }

  createdPolManagement(data) {
    return this.post("/pol-management/create", data);
  }

  updatePolManagement(data) {
    return this.put("/pol-management/update", data);
  }

  deletePolManagements(data) {
    return this.del("/pol-management/delete", data);
  }

  issuePol(data) {
    return this.post("/pol-management/issue", data);
  }

  replenishPol(data) {
    return this.post("/pol-management/replenish", data);
  }

  getVehiclePlannings(data) {
    return this.get("/vehicle-plannings", data);
  }

  createVehiclePlanning(data) {
    return this.post("/vehicle-plannings/create", data);
  }

  updateVehiclePlanning(data) {
    return this.post("/vehicle-plannings/update", data);
  }
}
