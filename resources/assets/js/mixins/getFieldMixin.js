import baseFields from 'components/fields/base';
import swapParentField from 'helpers/swapParentField';

export default {

	inject: ['fieldType'],

	methods: {
		getField(type) {
			return (
				baseFields[type] ?
					swapParentField(baseFields[type], this.fieldType) :  {
						name: type,
						template: '<div>This field type does not exist</div>'
					}
			);
		}
	}

};
