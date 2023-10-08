import Vue from 'vue';
import Vuex from 'vuex';
import rf from 'requestfactory';
import Const from 'common/Const';

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    user            : {},
    isInspector     : false,
  },
  getters: {
  },
  mutations: {
    updateUser(state, data) {
      state.user = data || {};
      state.isInspector = state.user.role === Const.USER_ROLE_INSPECTOR;
    }
  },
  actions: {
    getCurrentUser({ commit }) {
      return new Promise((resolve) => {
        rf.getRequest('UserRequest').getCurrentUser().then((res) => {
          commit('updateUser', res.data);
          resolve(res.data);
        });
      });
    },

    init() {
      if (window.isAuthenticated) {
        this.dispatch('getCurrentUser');
      }
    },
  },
});

export default store;
