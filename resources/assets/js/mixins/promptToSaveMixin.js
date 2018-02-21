/**
 * Mixin to display a suitable modal dialog to either allow navigating away from a page with unsaved changes
 * or to cancel and stay on that page.
 *
 * The component using this mixin MUST:
 * - Be a route-level component (used as part of a route) as the beforeRoute* callbacks only get called for these components.
 * - Override the isUnsaved() computed property to return true or false depending on whether they have unsaved changes.
 *
 * The component using this mixin MAY:
 * - Modify the dialog box title, message and button texts and type by specifying the relevant attributes in its own data
 * - Use any method to determine what counts as unsaved(). One example strategy is:
 *   - Ensure the component isn't in its initialisation / loading state
 *   - JSON.stringify() the relevant data on initial load and on saving
 *   - Compare this with the JSON.stringify() version of the current version of the data
 */
export default {

	beforeRouteLeave(to, from, next) {
		this.promptToSave(next);
	},

	beforeRouteUpdate(to, from, next) {
		this.promptToSave(next);
	},

	data() {
		return {
			// override any of these in the component using this mixin's data() to modify the prompt messages
			// vue only does a shallow merge https://vuejs.org/v2/guide/mixins.html#Option-Merging
			savePromptTitle: 'Are you sure you want to leave?',
			savePromptMessage: 'There are unsaved changes',
			savePromptConfirmButtonText: 'OK',
			savePromptCancelButtonText: 'Cancel',
			savePromptType: 'warning'
		};
	},

	computed: {
		/**
		 * Override this wherever you use this mixin
		 * @returns {boolean}
		 */
		isUnsaved() {
			return false;
		}
	},

	methods: {
		promptToSave(callback) {
			if(this.isUnsaved) {
				return this.$confirm(
					this.savePromptMessage,
					this.savePromptTitle,
					{
						confirmButtonText: this.savePromptConfirmButtonText,
						cancelButtonText: this.savePromptCancelButtonText,
						type: this.savePromptType
					}
				).then(() => {
					if(callback) {
						callback(true);
					}
				}).catch(() => {
					if(callback) {
						callback(false);
					}
				});
			}

			callback(true);
			return true;
		}
	}
};
