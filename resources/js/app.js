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

import PortalVue from 'portal-vue'
Vue.use(PortalVue)

const app = new Vue({
    el: '#app'
});
