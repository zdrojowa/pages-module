import Vue from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue';
import MultiSelect from 'vue-multiselect'

window.axios = require('axios');

Vue.use(CKEditor);

Vue.component('multiselect', MultiSelect);
Vue.component('editor', require('./components/editor.vue').default);

const app = new Vue({
    el: '#app'
});

