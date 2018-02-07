import baseFields from 'components/fields/base';

export default {

	methods: {
		getField(type) {
			return (
				baseFields[type] ?
					baseFields[type] :  {
						name: type,
						template: '<div>This field type does not exist</div>'
					}
			);
		}
	}

};
