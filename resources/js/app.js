/* global require Vue */
/*eslint no-undef: "warn"*/
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('login-form', require('./components/LoginForm.vue'));
Vue.component('main-paper-uploader', require('./components/MainPaperUploader.vue'));
Vue.component('course-viewer', require('./components/CourseViewer.vue'));
Vue.component('paper-list', require('./components/PaperList.vue'));
Vue.component('paper-heading', require('./components/PaperHeading.vue'));
Vue.component('add-local-user', require('./components/AddLocalUser.vue'));
Vue.component('add-external-user', require('./components/AddExternalUser.vue'));
Vue.component('staff-course-editor', require('./components/StaffCourseEditor.vue'));
Vue.component('wlm-importer', require('./components/WlmImporter.vue'));
Vue.component('user-list', require('./components/UserList.vue'));
Vue.component('impersonate-button', require('./components/ImpersonateButton.vue'));
Vue.component('admin-toggle-button', require('./components/AdminToggleButton.vue'));

import PortalVue from 'portal-vue'
Vue.use(PortalVue)

import vSelect from 'vue-select'
Vue.component('v-select', vSelect)


Echo.private('notifications')
    .listen('WlmImportComplete', (e) => {
        Vue.toasted.show('WLM Data Imported Successfully!');
    });

import Toasted from 'vue-toasted';
Vue.use(Toasted, {
    theme: "primary",
    position: "bottom-right",
    duration: 5000
});

import * as Sentry from '@sentry/browser'

Sentry.init({
    dsn: process.env.MIX_SENTRY_DSN,
    integrations: [new Sentry.Integrations.Vue({ Vue })]
})

const app = new Vue({
    el: '#app'
});
