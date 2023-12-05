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
          <tr style="border: 1px solid #b3c6e7; text-align: center;">
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">No.</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Trans Date</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Item Details</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Part #</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Quantity</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Location</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Item type</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Load/Hydrostatic Test Due</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Expiry Date</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Calibration Date</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Return By</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Trans</th>
            <th style="background-color: #b3c6e7; font-size: 12px; border:1px solid #b3c6e7; ">Status</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class=" border text-break text-center" style="font-size: 12px;">{{ index + 1 }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.transaction.created_at |
              dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.spares != null ? item.spares.name
              : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.spares != null ?
              item.spares.part_no : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.quantity != null ? item.quantity :
              "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;"> {{ `${item.transaction.cluster.name ||
              'N/A'} - ${item.shelf.name || 'N/A'} - ${item.bin.row || 'N/A'} - ${item.bin.bin || 'N/A'}` }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;"> {{ item.spares != null ?
              item.spares.label : "N/A" }}</td>

            <td class=" border text-break text-center" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.load_hydrostatic_test_due">
                {{ item.configures.load_hydrostatic_test_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>

            <td class=" border text-break text-center" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.expiry_date">
                {{ item.configures.expiry_date | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>
            <td class=" border text-break text-center" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.calibration_due">
                {{ item.configures.calibration_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
            </td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.transaction.user != null ?
              item.transaction.user.name : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">
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
            <td class=" border text-break text-center" style="font-size: 12px; text-transform: capitalize;">
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
