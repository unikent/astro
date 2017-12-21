import SnackBar from '../components/SnackBar';
import { isIframe, win } from 'classes/helpers';

/* global document */

export default (Vue, el = false) => {
	let snack;

	if(!isIframe) {
		const snackbar = new (Vue.extend(SnackBar));
		snackbar.vm = snackbar.$mount();
		win.snackbar = snack = snackbar.vm;
		(el ? el : document.body).appendChild(snackbar.vm.$el);
	}
	else {
		snack = win.top.snackbar;
	}

	Object.defineProperties(Vue.prototype, {
		$snackbar: {
			get() {
				return snack;
			}
		}
	});
};
