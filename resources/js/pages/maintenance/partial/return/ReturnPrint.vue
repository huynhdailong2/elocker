<template>
  <div id="returns-items-print" :class="{ 'd-none': !isPrinting, 'd-block': isPrinting }" v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
        <h2 class="text-center mb-4">
          <span class="font-weight-bold">Return Items</span>
        </h2>
      </div>
      <div class="row">
        <table class="" style="width: 100%;">
          <tr>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">No.</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Trans Date</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Details</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Part #</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Quantity</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Location</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item type</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Load/Hydrostatic Test Due</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Expiry Date</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Calibration Date</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Return By</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Trans</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Status</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ index + 1 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.transaction.created_at |
              dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.spares != null ? item.spares.name
              : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.spares != null ?
              item.spares.part_no : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.quantity != null ? item.quantity :
              "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;"> {{ `${item.transaction.cluster.name ||
              'N/A'} - ${item.shelf.name || 'N/A'} - ${item.bin.row || 'N/A'} - ${item.bin.bin || 'N/A'}` }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;"> {{ item.spares != null ?
              item.spares.label : "N/A" }}</td>

            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.load_hydrostatic_test_due">
                {{ item.configures.load_hydrostatic_test_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>

            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.expiry_date">
                {{ item.configures.expiry_date | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.calibration_due">
                {{ item.configures.calibration_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.transaction.user != null ?
              item.transaction.user.name : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">
              <div v-if="item.transaction.type === 'issue'">
                <span v-if="item.spares.type === 'consumable'">
                  {{ "I" }}
                </span>
                <span v-else>
                  {{ "L" }}
                </span>
              </div>
              <div v-if="item.transaction.type === 'return'">
                <span>
                  {{ "R" }}
                </span>
              </div>
              <div v-if="item.transaction.type === 'replenish'">
                <span>
                  {{ "RP" }}
                </span>
              </div>
            </td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;text-transform: capitalize;" >
              {{ item.conditions !== null ? item.conditions : "N/A" }}
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
import moment from 'moment'

export default {
  props: {
    config: {
      type: Object,
      default: () => { withoutQty: false }
    },

    data: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      isPrinting: false,
      current: moment().format('DD-MM-YYYY HH:mm')
    };
  },

  methods: {
    print() {
      this.isPrinting = true;
      this.$nextTick(() => {
        this.isPrinting = false;
        this.htmlToPaper(() => {
          this.$emit('CycleCount:closed');
        });
      })
    },

    htmlToPaper(callback) {
      let
        name = '_blank',
        specs = ['fullscreen=yes', 'titlebar=yes', 'scrollbars=yes'],
        replace = true,
        styles = ['/css/app.css'];
      specs = !!specs.length ? specs.join(',') : '';

      const element = document.getElementById('returns-items-print');

      if (!element) {
        this.isPrinting = false;
        alert(`Element to print #returns-items-print not found!`);
        return;
      }

      const url = '';
      const win = window.open(url, name, specs, replace);

      win.document.write(`
          <html>
            <head>
              <title>Return Items - ${this.current}</title>
            </head>
            <body>
              ${element.innerHTML}
            </body>
          </html>
        `);

      this.addStyles(win, styles);

      setTimeout(() => {
        win.document.close();
        win.focus();
        win.print();
        win.close();
        callback();
      }, 1000);
      return true;
    },

    addStyles(win, styles) {
      styles.forEach(style => {
        let link = win.document.createElement('link');
        link.setAttribute('rel', 'stylesheet');
        link.setAttribute('type', 'text/css');
        link.setAttribute('href', style);
        win.document.getElementsByTagName('head')[0].appendChild(link);
      });
    }
  }
}
</script>
