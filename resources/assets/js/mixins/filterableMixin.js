/*
 * To use this mixin, a component needs:
 * - A computed property called "items" containing items we want to filter.
 * - A data/computed property called "filters", which is an array of strings
 *   that represent the nested props to filter by.
 *
 * To use the filter, you simply need to update the "searchInput" property.
 */
import _ from 'lodash';

export default {

	created() {
		if(!this.items || !Array.isArray(this.items)) {
			throw new Error(
				'[filterableMixin] "items" must exist in your data object and be an array'
			);
		}
	},

	data() {
		return {
			// these are just generic filters to be replaced
			filters: ['name', 'username', 'email'],
			searchInput: ''
		};
	},

	computed: {
		filteredItems() {
			return (
				this.searchInput.length > 1 ?
					this.items.filter(
						this.createFilter(
							this.filters,
							this.searchInput.toLowerCase()
						)
					) :
					this.items
			);
		}
	},

	methods: {
		createFilter(searchKeys, searchTerm) {
			return user => searchKeys.some(
				key => {
					const value = _.get(user, key);
					return value && value.toLowerCase().indexOf(searchTerm) !== -1;
				}
			);
		}
	}

};
