/*
 * Mixin for wrapping a set of actions around a prompt to save if the user has
 * unsaved changes on the current page.
 */
import { mapMutations, mapGetters } from 'vuex';

export default {

	computed: {
		...mapGetters([
			'unsavedChangesExist'
		]),

		...mapMutations([
			'resetCurrentSavedState'
		]),
	},

	methods: {

		/**
		 * wraps a prompt to save arounds a series of actions
		 * @param {function} to be executed if the user elects to ignore unsaved changes - for example to
		 * leave the current page or log out
		 */
		promptToSave(intendedActions) {
			if(this.unsavedChangesExist()) {
				this.$confirm(
					'You have unsaved changes, you will lose them if you continue',
					'Warning',
					{
						confirmButtonText: 'Continue and lose changes',
						cancelButtonText: 'Cancel',
						type: 'warning'
					}
				).then(() => {
					// user decided not to save so reset the tracking of currently staved state
					this.$store.commit('resetCurrentSavedState');
					intendedActions();
				}).catch((error) => {
					// TODO: either user cancels or the actions within the then have thrown an exception
				});
			}
			else {
				// user had no unsaved changes so proceed with intended actions
				intendedActions();
			}
		},
	}
};
