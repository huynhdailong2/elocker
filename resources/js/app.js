
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import 'common/bootstrap';
import 'common/filters';

import Vue from 'vue';
import VueBroadcast from 'common/VueBroadcast';
import store from './store';
import Routers from './routes';

import BigNumber from 'bignumber.js';
import VueRouter from 'vue-router';
import VeeValidate from 'vee-validate';
import VModal from 'vue-js-modal';
import VTooltip from 'v-tooltip'

import App from './pages/App';
import AuthenticationUtils from 'common/AuthenticationUtils';
import Toasted from 'vue-toasted'

import DataTable from './pages/common/DataTable';
import SelectBox from './pages/common/SelectBox';

Vue.component('data-table', DataTable);
Vue.component('select-box', SelectBox);

import DataTable2 from './pages/common/DataTable2';
Vue.component('data-table2', DataTable2);

import vSelect from 'vue-select';
import Const from "./common/Const";
Vue.component('v-select', vSelect);

const toastedOptions = {
  theme: "outline",
  position: "top-right",
  icon: 'notification_important',
  duration: 2000
}

Vue.use(VTooltip)
Vue.use(VueRouter);
Vue.use(VueBroadcast);
Vue.use(Toasted, toastedOptions);
Vue.use(VeeValidate);
Vue.use(VModal, { dialog: true, injectModalsContainer: true, dynamicDefaults: { clickToClose: false } });


const router = new VueRouter(Routers);
router.beforeEach((to, from, next) => {

  if (to.meta.auth && !window.isAuthenticated) {
    return next({ path: '/login' });
  }

  let role = window.localStorage.getItem('role');
  if (role && role == Const.USER_ROLE_INSPECTOR) {
    Routers.urls_dined.forEach((item) => {
      if (item.role_id == role) {
        item.urls.forEach((evt) => {
          if (evt.url == to.fullPath) {
            return next({ path: '/404' });
          }
        })
      }
    });
  }

  if (to.meta.guest && window.isAuthenticated) {
    return next({ path: '/' });
  }
  return next();
});

Vue.mixin({
  data () {
    return {
      isSubmitting: false,
    };
  },
  methods: {
    startSubmit () {
      this.isSubmitting = true;
    },

    endSubmit () {
      this.isSubmitting = false;
    },

    getSubmitName (name) {
      return this.isSubmitting ? 'Processing' : name;
    },

    showSuccess (message = 'Successful!') {
      this.$toasted.show(
        message,
        {
          position: 'top-right',
          duration: 3000,
          className: 'toasted-success',
          iconPack: 'custom-class',
          icon: 'iconmo-checked toasted-icon',
        },
      );
    },

    showError (message = 'Something Wrong! Please check it again.') {
      this.$toasted.show(
        message,
        {
          position: 'top-right',
          duration: 3000,
          className: 'toasted-fail',
          iconPack: 'custom-class',
          icon: 'iconmo-close toasted-icon',
        },
      );
    },

    confirmAction(param = {}) {
      this.$modal.show('confirmation-modal', param)
    }
  }
});

window.app = new Vue({
  store,
  router,
  created () {
    BigNumber.config({ ROUNDING_MODE: BigNumber.ROUND_HALF_UP });
    this.$store.dispatch('init');
  },
  render: function(createElement) {
    return createElement(App);
  }
}).$mount('#app');
