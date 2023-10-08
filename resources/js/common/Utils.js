import XLSX from 'xlsx';
import moment from 'moment'
import { padStart, isEmpty } from 'lodash'

const DATETIME_PATTERN = 'DD-MM-YYYY HH:mm'

export default {

  exportExcel (data, nameFile, extension = '.xls') {
    data = data || [];
    const sheet = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, sheet, nameFile);
    XLSX.writeFile(wb, `${nameFile}${extension}`);
  },

  formatArrayToJson (records) {
    const fields = _.first(records);
    records.splice(0, 1);
    return _.map(records, (record) => {
      const recordObj = {};
      _.forEach(fields, (field, index) => {
        if (!!record[index] && !isNaN(record[index]) && !_.isEmpty(_.trim(record[index]))) {
          record[index] = this.formatCurrencyAmount(record[index], '', '0');
        }
        recordObj[field] = record[index];
      });
      return recordObj;
    });
  },

  async asyncForEach(array, callback) {
    for (let index = 0; index < array.length; index++) {
      await callback(array[index], index, array);
    }
  },

  stringTime2Object (time) {
    const date = new moment.utc(`10-10-2020 ${time}`, 'DD-MM-YYYY HH:mm');

    const hour    = padStart(date.hour(), 2, '0');
    const minute  = padStart(date.minute(), 2, '0');

    return {HH: `${hour}`, mm: `${minute}`};
  },

  objTime2String (obj) {
    if(isEmpty(obj) || typeof obj === 'string' || obj instanceof String) return obj
    return `${obj.HH}:${obj.mm}`
  },

  utcToClient (datetime, pattern = DATETIME_PATTERN) {
    if (!datetime) {
      return null
    }
    const stillUtc = moment.utc(datetime, pattern)
    return moment(stillUtc).local().toDate()
  },

  clientToUtc (datetime) {
    if (!datetime) {
      return null
    }
    return moment(datetime).utc().format('YYYY-MM-DD HH:mm')
  }

}
