<template>
	<el-select
		v-model="selected"
		multiple
		filterable
		:placeholder="placeholder"
		:filter-method="filterItems"
		:no-data-text="noItemsText"
	>
		<el-option
			v-for="item in filteredItems"
			:key="_.get(item, keyPath)"
			:label="_.get(item, labelPath)"
			:value="_.get(item, valuePath)"
		>
			<slot name="item" :item="item"></slot>
		</el-option>
	</el-select>
</template>

<script>
import _ from 'lodash';

export default {

	name: 'custom-multi-select',

	props: [
		'value',
		'items',
		'label-path',
		'value-path',
		'key-path',
		'placeholder',
		'filter-callback',
		'no-data-text',
		'no-match-text'
	],

	data() {
		return {
			searchFor: ''
		};
	},

	computed: {

		// slightly hacky way of using lodash directly in template
		_() {
			return _;
		},

		selected: {
			get() {
				return this.value;
			},
			set(value) {
				this.$emit('input', value);
			}
		},

		filteredItems() {
			return this.searchFor !== '' ?
				this.items.filter(item => this.filterCallback(item, this.searchFor)) :
				this.items;
		},

		noItemsText() {
			return this.items.length > 0 && this.filteredItems.length === 0 ?
				this.noMatchText : this.noDataText;
		}

	},

	methods: {

		filterItems(value) {
			this.searchFor = value;
		}

	}
};
</script>