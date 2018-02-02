import { mapMutations } from 'vuex';

export default {

	methods: {
		...mapMutations([
			'updateFieldValue'
		])
	},

	created() {
		this.internalChange = false;
		this.inlineFields = {};
	},

	mounted() {
		Object.keys(this.inlineFields).forEach(fieldName => {
			// TODO: replace contenteditable with another instance of scribe
			this.inlineFields[fieldName].setAttribute('contenteditable', true);

			this.inlineFields[fieldName].addEventListener('input', (e) => {

				this.updateFieldValue({
					name: fieldName,
					value: e.target.innerHTML
				});

				this.internalChange = true;
			});
		});
	}

};
