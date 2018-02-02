import fields from 'components/fields';

export default {

	methods: {
		getField(type) {
			return (
				fields[type] ?
					fields[type] :  {
						name: type,
						template: '<div>This field type does not exist</div>'
					}
			);
		}
	}

};
