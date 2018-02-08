import BlockField from 'components/BlockField';
import ProfileField from '@profiles/components/ProfileField';

const availableFields = {
	block: BlockField,
	profile: ProfileField
};

export default (field, type) => {
	if(!field) {
		return null;
	}

	return {
		...field,
		extends: availableFields[type || 'block']
	};
};
