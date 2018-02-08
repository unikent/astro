import { mapState } from 'vuex';

import EditOptions from 'components/EditOptions';

export default {

	name: 'edit-profile',

	extends: EditOptions,

	computed: {
		...mapState({
			currentIndex: state => state.contenteditor.currentBlockIndex
		}),

		definition() {
			return this.$store.state.profile.definition;
		},

		currentItem() {
			return this.$store.state.profile.profileData;
		},

		identifier() {
			return this.definition.name;
		}
	}

};
