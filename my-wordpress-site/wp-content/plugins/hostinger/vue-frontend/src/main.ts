import { createPinia } from "pinia";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import { createApp } from "vue";

import router from "@/router";

import "@/scss/main.scss";
import "vue3-toastify/dist/index.css";

import App from "./App.vue";

const initializeVueApp = () => {
	const pinia = createPinia();
	pinia.use(piniaPluginPersistedstate);

	const app = createApp(App);
	app.use(pinia);
	app.use(router);

	app.config.globalProperties.window = window;

	app.mount("#hostinger-tools-vue-app");
};

document.addEventListener("DOMContentLoaded", () => {
	initializeVueApp();
});
