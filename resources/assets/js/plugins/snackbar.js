import SnackBar from '../components/SnackBar.vue';

/* global document */

export default (Vue, el = false) => {
	const snackbar = new (Vue.extend(SnackBar));
	snackbar.vm = snackbar.$mount();

	Object.defineProperties(Vue.prototype, {
		$snackbar: {
			get() {
				return snackbar.vm;
			}
		}
	});

	(el ? el : document.body).appendChild(snackbar.vm.$el);
};
