const mapStuff = (map) => {
	const res = {};

	console.log(this);

	normalizeMap([map]).forEach(({ key, val }) => {
		res[key] = function mappedState() {
			console.log(this);
			let state = this.$store.state
			let getters = this.$store.getters

			return typeof val === 'function'
				? val.call(this, state, getters)
				: state[val]
		}

		res[key].vuex = true
	});

	return res;
};

function normalizeMap (map) {
	return Array.isArray(map)
		? map.map(key => ({ key, val: key }))
		: Object.keys(map).map(key => ({ key, val: map[key] }))
}


export default {

	props: ['name', 'index', 'fields', 'other'],

	data() {
		return Object.assign({}, this.fields);
	},

	computed() {
		console.log(this);
		return {
			test() {
				return this.fields.quote;
			}
		};
		// ...mapStuff('page'),
		// ...test('fields')
	},

	beforeCreate() {
		console.log(this);
		this.fieldKeys = Object.assign({}, Object.keys(this.$options.propsData.fields));
	},

	created() {
		// console.log(this.fields);

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

// const mapFields = () => {
// 	const mappings = {};

// 	Object.keys(this.fields).map((name) => {
// 		mappings[name] = (state) => state.page.blocks[this.index].fields[name];
// 	});

// 	return mappings;
// };