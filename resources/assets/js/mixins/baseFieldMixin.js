import { debug } from 'classes/helpers';

export default {

	props: ['field', 'path', 'errors'],

	data() {
		return {
			placeholder: null,
			anchor_link: null,
			...this.field
		};
	},

	computed: {
		value: {
			get() {
				return this.getFieldValue(this.path);
			},
			set(value) {
				this.updateFieldValue(this.path, this.transformValue(value));
			}
		}
	},

	methods: {
		getFieldValue(path) {
			debug(path);
			return null;
		},

		updateFieldValue(path, value) {
			debug(path, value);
		},

		// Here so we can override this at some point
		transformValue(value) {
			return value;
		},

		isRequiredField(definition) {
			return definition.validation && definition.validation.includes('required');
		},

		getRules() {
			return null;
		},

		getError() {
			return null;
		}
	}

};
