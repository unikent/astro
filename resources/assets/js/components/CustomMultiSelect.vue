<template>
	<el-select
		v-model="selected"
		multiple
		filterable
		:placeholder="placeholder"
		:filter-method="filterItems"
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
		'placeholder'
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

		// TODO: make generic (based on props)
		filteredItems() {
			return this.searchFor !== '' ? this.items.filter(
				item => ['name', 'username', 'email'].some(
					key => item.user[key].toLowerCase().indexOf(this.searchFor.toLowerCase()) !== -1
				)
			) : this.items;
		}

	},

	methods: {

		filterItems(value) {
			this.searchFor = value;
		}

	}
};
</script>