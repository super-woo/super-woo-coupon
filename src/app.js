import Vue from "vue";
window.axios = require("axios");
window.Qs = require('qs');
import Toasted from 'vue-toasted';

Vue.use(Toasted)

Vue.component("wactype", require("./components/wactype.vue").default);
Vue.component("wacfilter", require("./components/wacfilter.vue").default);
Vue.component("wacdiscount", require("./components/wacdiscount.vue").default);
Vue.component("wacrules", require("./components/wacrules.vue").default);
Vue.component("wactabs", require("./components/wactabs.vue").default);

new Vue({
  el: "#post",
  data: {
    wac_form: {
      type: "product"
    }
  }
});
