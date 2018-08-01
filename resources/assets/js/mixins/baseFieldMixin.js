import { debug } from 'classes/helpers';

export default {

	props: ['field', 'path', 'errors', 'currentDefinition'],

	data() {
		return {
			placeholder: null,
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
		}
	}

};
