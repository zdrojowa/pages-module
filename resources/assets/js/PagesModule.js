import Vue from 'vue';
import CKEditor from '@ckeditor/ckeditor5-vue2';
import {BootstrapVue, BootstrapVueIcons, IconsPlugin} from 'bootstrap-vue';
import CountryFlag from 'vue-country-flag'
import VueRouter from 'vue-router'
import PageIndex from './components/PageIndex'
import PageTab from './components/PageTab'
import TypesIndex from './components/TypesIndex'
import Type from './components/Type'
import MenuIndex from './components/MenuIndex'
import MenuTab from './components/MenuTab'

window.axios = require('axios');

Vue.use(CKEditor)
Vue.use(BootstrapVue)
Vue.use(BootstrapVueIcons)
Vue.use(VueRouter)
Vue.use(CountryFlag)
Vue.use(require('vue-moment'))

const app = document.getElementById('app');

new Vue({
    components: {PageIndex, PageTab, TypesIndex, Type, MenuIndex, MenuTab}
}).$mount(app);