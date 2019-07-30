import baseFields from 'components/fields/base';
import swapParentField from 'helpers/swapParentField';

export default {

	inject: ['fieldType'],

	computed: {
		fieldsTypes() {
			let f = {};

			Object
				.keys(baseFields)
				.forEach(
					type => f[type] = swapParentField(baseFields[type], this.fieldType)
				);

			return f;
		}
	},

	methods: {
		getField(type) {
			return (
				this.fieldsTypes[type] || {
					name: type,
					template: '<div>This field type does not exist</div>'
				}
			);
		}
	}

};
