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
			return a[prop] === b[prop] ? 0 : (a[prop] < b[prop] ? -1 : 1);
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
		paginatableItems() {
			const items = this.filteredItems || this.items;

			if(this.sorting.prop) {
				return (
					// concat is so the array isn't sorted in-place
					this.sorting.order === 'desc' ?
						items.concat().sort(this.sortMethod).reverse() :
						items.concat().sort(this.sortMethod)
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
					this.paginatableItems :
					this.paginatableItems.slice(from, to)
			);
		},

		total() {
			return this.paginatableItems.length;
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
			const sorting = {
				prop,
				order: order === 'ascending' ? 'asc' : 'desc'
			};

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
