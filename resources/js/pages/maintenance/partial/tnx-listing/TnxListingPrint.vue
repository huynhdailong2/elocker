<template>
  <div id="tnx-items-print" :class="{ 'd-none': !isPrinting, 'd-block': isPrinting }" v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
        <h2 class="mb-4">
          <span class="font-weight-bold">Trans Listing Count</span>
        </h2>
      </div>

      <div class="row">
        <table class="" style="width: 100%;">
          <tr>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">NO.</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Trans Date</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">WO#</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Vehicle#</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Platform</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Location</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Type</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Details</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Part No</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Quantity</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Area Use</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">By</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Trans</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Expiry</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class=" border text-break text-center" style="font-size: 12px;">{{ index + 1 }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.transaction.created_at |
              dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.job_card != null ?
              item.job_card.wo : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.vehicle != null ?
              item.vehicle.vehicle_num : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.job_card != null ?
              item.job_card.platform : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;"> {{ `${item.transaction.cluster.name ||
              'N/A'} - ${item.shelf.name || 'N/A'} - ${item.bin.row || 'N/A'} - ${item.bin.bin || 'N/A'}` }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;"> {{ item.spares != null ?
              item.spares.label : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.spares != null ? item.spares.name
              : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.spares != null ?
              item.spares.part_no : "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.quantity != null ? item.quantity :
              "N/A" }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.torque_wrench_area != null ?
              item.torque_wrench_area.area : "N/A" }}</td>
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
            <td class=" border text-break text-center" style="font-size: 12px;">
              <div class="text ellipsis" v-if="item.configures != null && item.configures.expiry_date">
                {{ item.configures.expiry_date | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                  'DD-MM-YYYY') || "N/A" }}
              </div>
              <div class="text ellipsis" v-else>
                {{ "N/A" }}
              </div>
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
        styles = ['/css/print.css'];
      specs = !!specs.length ? specs.join(',') : '';

      const element = document.getElementById('tnx-items-print');

      if (!element) {
        this.isPrinting = false;
        alert(`Element to print #tnx-items-print not found!`);
        return;
      }

      const url = '';
      const win = window.open(url, name, specs, replace);

      win.document.write(`
          <html>
            <head>
              <title>Trans Listing Count - ${this.current}</title>
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
