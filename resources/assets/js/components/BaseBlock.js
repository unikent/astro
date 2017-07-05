import inlineFieldMixin from 'mixins/inlineFieldMixin';

export default {

	props: [
		'type',
		'index',
		'fields',
		'other'
	],

	mixins: [inlineFieldMixin],

	data() {
		return { ...this.fields };
	},

	created() {
		this.fieldElements = {};
		this.watching = {};
		this.watchFields(this.fields);

		// should only be triggered when all fields are overwitten
		this.$watch('fields', () => {
			this.watchFields(this.fields);
		});
	},

	methods: {
		watchFields(fields) {
			Object.keys(fields).map((name) => {
				if(!this.watching[name]) {
					this.watching[name] = true;

					this.$watch(`fields.${name}`, (newVal) => {
						if(this.internalChange) {
							this.internalChange = false;
							return;
						}

						this[name] = newVal;
					}, {
						deep: true
					});
				}
			});
		}
	}

};
