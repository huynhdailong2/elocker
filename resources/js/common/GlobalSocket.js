import Vue from 'vue';
import rf from 'requestfactory';

export default class GlobalSocket {

  constructor() {
    // Public channels
    //this.listenForMessage();

    // Private channels
    if (window.isAuthenticated) {
      rf.getRequest('UserRequest').getCurrentUser()
        .then(res => {

          const userId = res.data.id;
        });
    }

    Vue.mixin({
      mounted: function () {
        if (this.getSocketEventHandlers) {
          window._.forIn(this.getSocketEventHandlers(), (handler, eventName) => {
            this.$on(eventName, handler);
          });
        }

        if (this.getEventHandlers) {
          window._.forIn(this.getEventHandlers(), (handler, eventName) => {
            this.$on(eventName, handler);
          });
        }
      },
      beforeDestroy() {
        if (this.getSocketEventHandlers) {
          window._.forIn(this.getSocketEventHandlers(), (handler, eventName) => {
            this.$off(eventName, handler);
          });
        }

        if (this.getEventHandlers) {
          window._.forIn(this.getEventHandlers(), (handler, eventName) => {
            this.$off(eventName, handler);
          });
        }
      }
    });
  }

  listenForBountyStarting() {
    window.Echo.channel('App.UserBounty.Started')
      .listen('BountyStartedEvent', (res) => {
        window.app.$broadcast('BountyStarted', res.data);
    });
  }

  // listenForNotification(userId) {
  //   window.Echo.private('App.User.' + userId)
  //     .notification((notification) => {
  //       window.app.$broadcast('NotificationUpdated', notification);
  //   });
  // }
}
