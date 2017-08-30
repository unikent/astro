/*
 mixin for wrapping a set of actions around a prompt to save if the user has unsaved changes on the current page 
*/
export default {
	methods: {
		/**
		 * wraps a prompt to save arounds a series of actions 
		 * @param {function} to be executed if the user elects to ignore unsaved changes - for example to
		 * leave the current page or log out
		 */
		promptToSave(intendedActions) {
			const unsavedChangesExist = this.unsavedChangesExist();
			if (unsavedChangesExist) {
				this.$confirm(`You have unsaved changes, you will lose them if you continue`, 'Warning', {
					confirmButtonText: 'Continue and lose changes',
					cancelButtonText: 'Cancel',
					type: 'warning'
				}).then(() => {
					// user decided not to save 
					intendedActions();
				}).catch(() => {
					// decided to remain in the editor
				});
			} else {
				// user had no unsaved changes so proceed with intended actions
				intendedActions();
			}
		},
	}
};
		