
import UserRequest from './UserRequest';
import AdminRequest from './AdminRequest';
import VehicleRequest from './VehicleRequest';
import SpareRequest from './SpareRequest';
import SettingRequest from './SettingRequest';
import WeightRequest from './WeightRequest';

const requestMap = {
  AdminRequest,
  VehicleRequest,
  SpareRequest,
  UserRequest,
  SettingRequest,
  WeightRequest,
};

const instances = {};

export default class RequestFactory {

  static getRequest(classname) {
    let RequestClass = requestMap[classname];
    if (!RequestClass) {
      throw new Error('Invalid request class name: ' + classname);
    }

    let requestInstance = instances[classname];
    if (!requestInstance) {
        requestInstance = new RequestClass();
        instances[classname] = requestInstance;
    }

    return requestInstance;
  }

}
