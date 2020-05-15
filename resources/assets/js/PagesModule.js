import Vue from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue';
import MultiSelect from 'vue-multiselect'
import draggable from 'vuedraggable';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';

window.axios = require('axios');

Vue.use(CKEditor);
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

Vue.component('multiselect', MultiSelect);
Vue.component('draggable', draggable);
Vue.component('media-selector', require('./components/media-selector.vue').default);
Vue.component('hiro', require('./components/hiro.vue').default);
Vue.component('modal', require('./components/modal.vue').default);
Vue.component('nested', require('./components/nested.vue').default);
Vue.component('gallery', require('./components/gallery.vue').default);
Vue.component('page-section', require('./components/page-section.vue').default);
Vue.component('editor', require('./components/editor.vue').default);
Vue.component('type', require('./components/type.vue').default);
Vue.component('edit-section', require('./components/edit-section.vue').default);

const app = new Vue({
    el: '#app'
});

