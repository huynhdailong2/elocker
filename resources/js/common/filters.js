import Vue from 'vue';
import moment from 'moment';
import Const from 'common/Const';
import numeral from 'numeral'
import { isNil, chain, isEmpty } from 'lodash'

Vue.filter('timestampToDate', function (timestamp, pattern = 'YYYY-MM-DD HH:mm:ss') {
  return moment.utc(timestamp, 'x').local().format(pattern);
});

Vue.filter('dateFormatter', function (value, parsePattern = 'YYYY-MM-DD HH:mm:ss', formatPattern = 'DD/MM/YYYY') {
    if (!value) {
        return value;
    }
  return moment.utc(value, parsePattern).local().format(formatPattern);
});

Vue.filter('timestampFormatter', function (timestamp, pattern = 'YYYY-MM-DD HH:mm:ss') {
    if (!timestamp) {
        return timestamp;
    }
  return moment.utc(timestamp).local().format(pattern);
});

Vue.filter('dateTimeFormatterLocal', function (value, parsePattern = 'YYYY-MM-DD HH:mm:ss', formatPattern = 'DD/MM/YYYY HH:mm:ss') {
  const date = moment(value, parsePattern)
  if (!value || value === 'null' || !date.isValid()) {
    return '';
  }
  return date.local().format(formatPattern);
});

// Vue.filter('timestampFormatter', function (timestamp) {
//   const datetime  = moment(timestamp);
//   const today     = new Date();

//   if (datetime.isSame(today, 'day')) {
//     return datetime.format('hh:mm A');
//   }

//   if (datetime.isSame(today, 'week')) {
//     return datetime.format('ddd');
//   }

//   if (!datetime.isSame(today, 'year')) {
//     return datetime.format('YYYY-MM-DD');
//   }

//   return datetime.format('DD MMM');
// });

Vue.filter( 'uppercase', function (value) {
  return window._.toUpper(value);
});

Vue.filter( 'upperFirst', function (value) {
  return window._.upperFirst(value);
});

Vue.filter( 'uppercaseFirst', function (value) {
  return window._.startCase(value);
});

Vue.filter('formatQuantity', function (amount, zeroValue, lengthDecimal = 6) {
  const numberOfDecimalDigits = lengthDecimal
  const format = numberOfDecimalDigits === 0
    ? '0,0'
    : '0,0.[' + Array(numberOfDecimalDigits + 1).join('0') + ']'
  if (isNil(zeroValue)) {
    zeroValue = ''
  }
  const round = (Math.floor(parseFloat(amount) * 100) / 100).toFixed(lengthDecimal)
  return (amount && parseFloat(round) !== 0) ? numeral(round).format(format) : zeroValue
})

Vue.filter('toNumber', function (value) {
  const number = parseFloat(value);
  if (isNaN(number)) {
    return value;
  }
  // is e number (Ex: 1e-7)
  if (number.toString().includes('e')) {
    const dot = '.';
    const strValue = `${value}`;
    if (strValue.indexOf(dot) === -1) {
      return strValue;
    }
    const trimEndZero = window._.trimEnd(strValue, '0');
    return window._.trimEnd(trimEndZero, dot);
  }
  return number.toFixed(1);
});

Vue.filter( 'formatUserRole', function (value) {

  const result = chain(Const.USER_ROLES)
    .filter(item => item.value === value)
    .head()
    .value();

  return result ? result.name : value
});

Vue.filter( 'formatItemType', function (value) {

  const result = chain(Const.ITEM_TYPE)
    .filter(item => item.value === value)
    .head()
    .value();

  return result ? result.name : value
});

Vue.filter( 'formatVehicleStatus', function (value) {
  switch(value) {
    case 1:
      return 'Passed';
    case 0:
      return 'Failed';
    default:
      return 'N/A';
  }
});


Vue.filter('shortNumber', function (value, length = 0) {
  const number = parseFloat(value)
  if (isNaN(number)) {
    return value
  }

  const units = [
    { milstoneValue: 1000, name: '', divValue: 1 },
    { milstoneValue: 1000000, name: 'K+', divValue: 1000 },
    { milstoneValue: 1000000000, name: 'M+', divValue: 1000000 },
    { milstoneValue: 1000000000000, name: 'B+', divValue: 1000000000 }
  ]

  for (let i = 0; i < units.length; i++) {
    const unit = units[i]
    if (number < unit.milstoneValue) {
      const result = Math.floor(number / unit.divValue)

      return `${result}${unit.name}`
    }
  }

  return '1T+'
})

Vue.filter('objTime2String', (obj) => {
  if(isEmpty(obj) || typeof obj === 'string' || obj instanceof String) return obj
  return `${obj.HH}:${obj.mm}`
})
