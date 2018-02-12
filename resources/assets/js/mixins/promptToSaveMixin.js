export default {

	beforeRouteLeave(to, from, next) {
		if(!this.loading && this.isUnsaved) {
			this.promptToSave(next);
		}
		else {
			next();
		}
	},

	data() {
		return {
			loading: false,
			initialData: ''
		};
	},

	computed: {
		isUnsaved() {
			return false;
		}
	},

	methods: {
		promptToSave(callback) {
			if(this.isUnsaved) {
				return this.$confirm(
					'Are you sure you want to leave?',
					'There are unsaved changes',
					{
						confirmButtonText: 'OK',
						cancelButtonText: 'Cancel',
						type: 'warning'
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
