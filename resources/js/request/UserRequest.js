import BaseModelRequest from './base/BaseModelRequest';

export default class UserRequest extends BaseModelRequest {

  getModelName() {
    return 'users'
  }

  login(username, password) {
    const params = {
      username: username,
      password: password,
    }
    return this.post('/login', params);
  }

  register({ email, password, passwordConfirmation, username, dob, sex}) {
    let params = {
      email: email,
      password: password,
      password_confirmation: passwordConfirmation,
      agree_term: 1,
      username:username,
      dob: dob,
      sex: sex
    }
    return this.post('/create-account', params);
  }

  getAllUsers(params) {
    return this.get('/users', params);
  }

  getUserInfoByCardId(params) {
    return this.get('/users/info/by-card-id', params);
  }

  updateAccount(params) {
    return this.post(`/users/${params.id}`, params);
  }

  createNewAccount(params) {
    return this.post('/users/create', params);
  }

  deleteUser(params) {
    return this.del(`/users/${params.id}`, params);
  }

  getCurrentUser(useCache=true, params) {
    if (this.user && useCache) {
      return new Promise((resolve, reject) => {
        resolve(this.user);
      });
    }

    return new Promise((resolve, reject) => {
      let url = '/users/info';
      var self = this;
      this.get(url, params)
        .then(function (user) {
          self.user = user;
          window.localStorage.setItem('role', user.data.role);
          resolve(user);
        })
        .catch(function (error) {
          reject(error);
        });
    });
  }

  getUserStatistics (params) {
    return this.get(`/users/statistic`, params);
  }

  exportUsers() {
    return this.downloadExcel('/users/export', 'users');
  }

  importUsers(params) {
    return this.post('/users/import', params);
  }
}
