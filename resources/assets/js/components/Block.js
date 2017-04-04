import { inlineFieldMixin } from '../libs/helpers';
import inlineEdit from '../directives/inline-edit';

export default {

	mixins: [inlineFieldMixin],

	props: ['name', 'index', 'fields', 'other'],

	directives: {
		inlineEdit
	},

	data() {
		return Object.assign({}, this.fields);
	},

	created() {
		Object.keys(this.fields).map((name) => {
			this.$watch(`fields.${name}`, (newVal, oldVal) => {
				console.log(`Changed ${name} field`);
				this[name] = newVal;
			}, {
				deep: true
			});
		});
	}

}