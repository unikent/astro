import { mapMutations } from 'vuex';

/* global $, window */

export default {

	methods: {
		...mapMutations([
			'updateFieldValue'
		]),

		markAsInline(name) {
			console.log(`Added ${this.index} - ${name}`)
		}
	},

	created() {
		// keep these private and untracked
		this.internalChange = false;
		this.inlineFields = {};
	},

	mounted() {
		const self = this;

		Object.keys(this.inlineFields).forEach(fieldName => {
			$(this.inlineFields[fieldName]).redactor({
				focus: true,
				toolbarExternal: window.parent.document.querySelector('#toolbar'),
				callbacks: {
					change() {
						console.log(this);
						self.updateFieldValue({
							index: self.index,
							name: fieldName,
							value: this.code.get()
						});
						self.internalChange = true;
					}
				}
			});
		});
	}

};
