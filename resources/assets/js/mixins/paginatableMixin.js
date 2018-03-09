export default {

	props: {
		counts: {
			type: Array,
			default: () => [20, 50, 100, 200]
		}
	},

	created() {
		if(!this.items || !Array.isArray(this.items)) {
			throw new Error('[paginatableleMixin] "items" must exist in your data object and be an array');
		}

		this.defaultSortMethod = (a, b) => {
			const prop = this.sorting.prop;
			return a[prop] < b[prop] ? -1 : (a[prop] > b[prop] ? 1 : 0);
		};
	},

	data() {
		return {
			currentPage: 1,
			count: 20,
			sorting: {
				prop: null
			}
		};
	},

	computed: {
		sortedItems() {
			const items = this.filteredItems || this.items;

			if(this.sorting.prop) {
				return (
					// shallow copy array so it isn't sorted in-place
					this.sorting.order === 'descending' ?
						[...items].sort(this.sortMethod).reverse() :
						[...items].sort(this.sortMethod)
				);
			}

			return items;
		},

		pagedItems() {
			const
				from = (this.currentPage - 1) * this.count,
				to = from + this.count;

			return (
				this.total < (to - from) ?
					this.sortedItems :
					this.sortedItems.slice(from, to)
			);
		},

		total() {
			return this.sortedItems.length;
		}
	},

	methods: {
		handleCountChange(newSize) {
			this.count = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		},

		handleSortChange({ column, prop, order }) {
			const sorting = { prop, order };

			if(
				this.sorting.prop === sorting.prop &&
				this.sorting.order === sorting.order
			) {
				return;
			}

			if(column && column.sortMethod) {
				this.sortMethod = column.sortMethod;
			}
			else {
				this.sortMethod = this.defaultSortMethod;
			}

			this.sorting = sorting;
		}
	}

};
