<template>
  <div id="issue-pols-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row text-center">
        <h2 class="mb-4">
          <span class="font-weight-bold">Issued POL - {{ current }}</span>
        </h2>
      </div>

      <div class="row" style="background-color: #b3c6e7;">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">S/N</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Card Number</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Material Number</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Description</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Type</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty OH</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty Issue</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Issue By</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Issue To</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-center text-dark">{{ index + 1 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.card_number }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.material_number }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.description }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.type | upperFirst }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.quantity_oh || 0 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.quantity || 0 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.issue_by }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.issue_to }}</div>
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
          this.$emit('IssuedPol:closed');
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

      const element = document.getElementById('issue-pols-print');

      if(!element) {
        this.isPrinting = false;
        alert(`Element to print #issue-pols-print not found!`);
        return;
      }

      const url = '';
      const win = window.open(url, name, specs, replace);

      win.document.write(`
        <html>
          <head>
            <title>Issue POLs - ${this.current}</title>
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
