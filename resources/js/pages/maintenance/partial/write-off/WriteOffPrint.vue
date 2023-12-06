<template>
  <div id="writeoff-items-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <h2 class="text-center mb-4">
        <span class="font-weight-bold">Write Off Items</span>
      </h2>
      </div>

      <div class="row">
        <table class="" style="width: 100%;">
          <tr>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">No.</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Details</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Part No</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Quantity</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Location</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Reason</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Write Off By</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Edited Time</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-size: 12px;">Item Type</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ index + 1 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.spares !== null ? item.spares.name : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.spares !== null ? item.spares.part_no : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.quantity || 0 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.cluster_name || "N/A" }}-{{ item.cabinet_name || "N/A" }}-{{ item.bin.row || "N/A"}}-{{ item.bin_name || "N/A"}}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.reason !== null ? item.reason : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.user !== null ? item.user.name : "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') || "N/A" }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark text ellipsis" style="font-size: 12px;">{{ item.spares ? item.spares.label : "N/A"  }}</td>
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
      print () {
        this.isPrinting = true;
        this.$nextTick(() => {
          this.isPrinting = false;
          this.htmlToPaper(() => {
            this.$emit('CycleCount:closed');
          });
        })
      },

      htmlToPaper (callback) {
        let
          name = '_blank',
          specs = ['fullscreen=yes','titlebar=yes', 'scrollbars=yes'],
          replace = true,
          styles = ['/css/app.css'];
        specs = !!specs.length ? specs.join(',') : '';

        const element = document.getElementById('writeoff-items-print');

        if(!element) {
          this.isPrinting = false;
          alert(`Element to print #writeoff-items-print not found!`);
          return;
        }

        const url = '';
        const win = window.open(url, name, specs, replace);

        win.document.write(`
          <html>
            <head>
              <title>Write Off Items - ${this.current}</title>
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

      addStyles (win, styles) {
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
