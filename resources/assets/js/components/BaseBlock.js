import inlineFieldMixin from '../mixins/inlineFieldMixin';
import inlineEdit from '../directives/inline-edit';

export default {

	// mixins: [inlineFieldMixin],

	props: ['name', 'index', 'fields', 'other'],

	directives: {
		inlineEdit
	},

	data() {
		return { ...this.fields };
	},

	created() {
		Object.keys(this.fields).map((name) => {
			this.$watch(`fields.${name}`, (newVal, oldVal) => {
				this[name] = newVal;
			}, {
				deep: true
			});
		});
	}

};
