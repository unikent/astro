import baseFields from 'components/fields/base';
import containerFields from 'components/fields/containers';
import displayFields from 'components/fields/display';
import { fields as externalFields } from 'helpers/themeExports';

export default {
	...baseFields,
	...containerFields,
	...displayFields,
	...externalFields
};
