import BlockField from 'components/BlockField';
import { addThemeFieldParentTypes } from 'helpers/themeExports';

const availableFields = addThemeFieldParentTypes({
	block: BlockField
});

export default (field, type) => {
	if(!field) {
		return null;
	}

	return {
		...field,
		extends: availableFields[type || 'block']
	};
};
