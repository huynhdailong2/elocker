import Vue from 'vue';
import VueMqtt from 'vue-mqtt';

Vue.use(VueMqtt, `ws://${process.env.MIX_MQTT_HOST}:${process.env.MIX_MQTT_PORT}`);

export default class MqttPubSub {

  constructor() {
    Vue.mixin({
      mqtt: {
         '+/device/weight/#': function(data, $topic) {
          console.log(String.fromCharCode.apply(null, data));
        },
      },
      mounted: function () {
        const prefixTopic = process.env.MIX_MOSQUITTO_PREFIX_MQTT_WEBSOCKETS;
        this.$mqtt.subscribe(`webapp/#`);


        // if (this.getSocketEventHandlers) {
        //   window._.forIn(this.getSocketEventHandlers(), (handler, eventName) => {
        //     this.$on(eventName, handler);
        //   });
        // }

        // if (this.getEventHandlers) {
        //   window._.forIn(this.getEventHandlers(), (handler, eventName) => {
        //     this.$on(eventName, handler);
        //   });
        // }
      },

      beforeDestroy() {
        // if (this.getSocketEventHandlers) {
        //   window._.forIn(this.getSocketEventHandlers(), (handler, eventName) => {
        //     this.$off(eventName, handler);
        //   });
        // }

        // if (this.getEventHandlers) {
        //   window._.forIn(this.getEventHandlers(), (handler, eventName) => {
        //     this.$off(eventName, handler);
        //   });
        // }
      }
    });
  }
}
