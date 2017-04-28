export default {
	bind(el, binding, vnode) {
		console.log('inline-edit');
		const {expression, value} = binding;
		el.innerHTML = value;

		const block = vnode.context;

		// block.markAsInline(expression);
		block.inlineFields[expression] = el;
	},

	update(el, { value, oldValue }, { context: block }) {

		if(value !== oldValue && !block.internalChange) {
			$(el).redactor('code.set', value);
			console.log(value);
		}

		block.internalChange = false;
	}
};
