import Vue from 'vue';

/* global console */
/* eslint-disable no-console */

Vue.directive('inline-edit', {
	bind(el, { expression, value }, { context: block }) {
		console.log(value);
		block.inlineFields[expression] = el;
	},

	update(el, { value, oldValue }) {
		if(value !== oldValue) {
			console.log('updated from outside', value, oldValue);
			el.classList.add('flash');
			// el.addEventListener('animationend', (e) => {
			// 	console.log(e);
			// 	e.target.classList.remove('flash');
			// })
		}
	}
});
