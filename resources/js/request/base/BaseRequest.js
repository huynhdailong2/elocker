import AuthenticationUtils from 'common/AuthenticationUtils';
import moment from "moment";

export default class BaseRequest {
  getUrlPrefix() {
    return '/api/v1';
  }

  getCurrentLocale() {
    if (window.i18n) {
      return window.i18n.locale;
    }
  }

  appendLocale (data) {
    const lang = this.getCurrentLocale();
    return Object.assign(data, { lang });
  }

  appendXAdmin (data) {
    return Object.assign(data, { headers: { 'x-amss-be': 1 }});
  }

  async get(url, params = {}, cancelToken) {
    try {
      const config = {
        params: params,
        cancelToken: cancelToken ? cancelToken.token : undefined,
        headers: { 'x-amss-be': 1 }
      };

      const response = await window.axios.get(this.getUrlPrefix('GET') + url, config);
      return this._responseHandler(response);
    } catch (error) {
      this._errorHandler(error);
    }
  }

  async put(url, data = {}) {
    try {
      // data = this.appendLocale(data);
      data = this.appendXAdmin(data);
      const response = await window.axios.put(this.getUrlPrefix() + url, data);
      return this._responseHandler(response);
    } catch (error) {
      this._errorHandler(error);
    }
  }

  async post(url, data = {}) {
    try {
      // data = this.appendLocale(data);
      data = this.appendXAdmin(data);
      const response = await window.axios.post(this.getUrlPrefix() + url, data);
      return this._responseHandler(response);
    } catch (error) {
      this._errorHandler(error);
    }
  }

  async del(url, data = {}) {
    try {
      // data = this.appendLocale(data);
      data = this.appendXAdmin(data);
      const response = await window.axios.delete(this.getUrlPrefix() + url, {data});
      return this._responseHandler(response);
    } catch (error) {
      this._errorHandler(error);
    }
  }

  async download (url, params = {}, cancelToken) {
    try {
      const config = {
        responseType: 'blob',
        params: params,
        cancelToken: cancelToken ? cancelToken.token : undefined,
        headers: { 'x-amss-be': 1 }
      };
      return await window.axios.get(this.getUrlPrefix('GET') + url, config);
    } catch (error) {
      this._errorHandler(error);
    }
  }

  async downloadExcel(url, prefixName) {
    const config = {
      headers: { 'x-amss-be': 1 },
      responseType: 'arraybuffer'
    };

    window.axios.get(this.getUrlPrefix('GET') + url, config).then(response => {
      const fileURL = window.URL.createObjectURL(new Blob([response.data]));
      const fileLink = document.createElement('a');
      const currentTime = moment().local().format('YYYY-MM-DD');
      const filename = `${prefixName}-${currentTime}.xlsx`;
      fileLink.href = fileURL;
      fileLink.setAttribute('download', filename);
      document.body.appendChild(fileLink);
      fileLink.click();
    });
  }

  async _responseHandler(response, url) {
    const data = response.data;
    if (response.status === 202) {
      data.redirectUrl = '/';
      window.app.$broadcast('BountyCounterModal', data);
    }
    return data;
  }

  // async download(url, params, fileName) {
  //   const response = await this.get(url, params);
  //   CsvUtils.export(response, fileName);
  // }

  _errorHandler(err) {
    window.app.$broadcast('EVENT_COMMON_ERROR', err);
    if (err.response && err.response.status === 401) { // Unauthorized (session timeout)
      AuthenticationUtils.removeAuthenticationData();
      window.app.$broadcast('UserSessionRegistered');
    }
    // if (err.response && err.response.status === 503) { // maintenance
    //   window.location.reload();
    // }
    throw err;
  }

}
