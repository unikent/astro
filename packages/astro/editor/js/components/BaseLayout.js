/**
base component for the them's layout components
used as a place to house any functionality requred across all the theme layouts
**/
import Region from 'components/Region';
import { disableForms } from 'classes/helpers';

export default {
	components: {
		Region
	},

	mounted() {
		// disable any form actions/buttons contained in the layout
		disableForms(this.$el);
	}
};

