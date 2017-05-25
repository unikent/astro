export default {

	props: [
		'type',
		'index',
		'fields',
		'other'
	],

	data() {
		return this.fields;
	},

	created() {
		this.watching = {};
		this.watchFields(this.fields);

		// should only be triggered when all fields are overwitten
		this.$watch('fields', () => {
			this.watchFields(this.fields);
		});

		// TODO: deal with fields that have nested data?
	},

	methods: {
		watchFields(fields) {
			Object.keys(fields).map((name) => {
				if(!this.watching[name]) {
					this.watching[name] = true;

					this.$watch(`fields.${name}`, (newVal) => {
						this[name] = newVal;
					}, {
						deep: true
					});
				}
			});
		}
	}

};
