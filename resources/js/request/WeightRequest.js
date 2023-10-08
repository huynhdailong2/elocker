import BaseModelRequest from './base/BaseModelRequest';

export default class WeightRequest extends BaseModelRequest {
  getSites () {
    return this.get('/weight/list-sites');
  }

  getBinsOfShelf (shelfId) {
    return this.get('/weight/get-bins/' + shelfId);
  }

  updateBinInformation (bin) {
    return this.put('/weight/update-bin/', bin);
  }

  getTransactionsWeighingSystem(params) {
    return this.get('/weight/transactions-weighing-system/', params);
  }

  sendWeighingSystemReport (params) {
    return this.post('/report/weighing-system/by-mail', params);
  }
}
