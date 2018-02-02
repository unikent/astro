import Vue from 'vue';

Vue.directive('field', {
	bind(el, { expression: field }, { context: block }) {

		if(typeof field === 'string') {
			block.fieldElements[field] = el;
		}
		else if(Array.isArray(field)) {
			field.forEach((f) => block.fieldElements[f] = el)
		}

	}
});
