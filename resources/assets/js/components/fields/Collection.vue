<template>
<div class="collection-field">
	<div
		v-for="(val, index) in currentValue"
		class="collection-field__field-list"
	>
		<template v-for="fieldDefinition in fields">
			<div class="ci-field">
				<el-form-item
					:label="fieldDefinition.label"
					:prop="`${name}.${index}.${fieldDefinition.name}`"
					:rules="getRules(fieldDefinition.name)"
					:error="getError(index, fieldDefinition.name)"
				>
					<template slot="label">
						<span>{{ fieldDefinition.label }}</span>
						<el-tooltip
							v-if="fieldDefinition.info"
							popper-class="el-tooltip__popper--narrow"
							:content="fieldDefinition.info"
							placement="top">
							<icon
								class="el-form-item__icon-help"
								name="help-circle"
								width="15"
								height="15"
								viewBox="0 0 15 15"
							/>
						</el-tooltip>
					</template>
					<component
						:is="getField(fieldDefinition.type)"
						:field="childField(fieldDefinition)"
						:path="`${name}.${index}.${fieldDefinition.name}`"
						:index="currentIndex"
						:key="`${name}-${fieldDefinition.name}-${currentIndex}`"
					/>
				</el-form-item>
			</div>
		</template>

		<div @click="removeItem(index)" class="block-overlay-hovered__delete block-overlay-hovered__delete--sidebar">
			<icon name="delete" width="20" height="20" />
		</div>
	</div>

	<div class="collection-add">
		<span class="collection-add__counter">
			{{ currentValue.length }} {{ this.label.slice(0, -1).toLowerCase() }}{{ currentValue.length === 1 ? '' : 's' }}
		</span>
		<el-button @click="addItem" class="collection-add__button">Add {{ this.label.slice(0, -1).toLowerCase() }}</el-button>
	</div>
</div>
</template>

<script>
import _ from 'lodash';
import Vue from 'vue';
import { mapState, mapGetters } from 'vuex';
import baseFieldMixin from 'mixins/baseFieldMixin';
import getFieldMixin from 'mixins/getFieldMixin';
import { Definition } from 'classes/helpers';

import Icon from '../Icon';

export default {

	name: 'collection-field',
	mixins: [baseFieldMixin, getFieldMixin],

	components: {
		Icon
	},

	computed: {
		...mapState({
			currentIndex: state => state.page.currentBlockIndex
		}),

		...mapGetters([
			'getCurrentFieldValue'
		]),

		rules() {
			return Definition.getRules(this.currentDefinition);
		},

		currentValue() {
			return this.getCurrentFieldValue(this.name);
		}
	},

	methods: {

		childField(field) {
			return _.pick(field, ['label', 'default', 'options']);
		},

		getRules(fieldName) {
			let rules = Array.isArray(this.rules[this.name]) ?
				this.rules[this.name][this.rules[this.name].length - 1] :
				this.rules[this.name];

			return rules.fields[fieldName];
		},

		addItem() {
			const
				item = {},
				value = _.cloneDeep(this.getCurrentFieldValue(this.name));

			this.fields.forEach(field => {
				item[field.name] = Definition.initialiseField(
					field.type || 'text', field.default
				);
			});

			value.push(item);

			this.updateAndValidate(value);
		},

		removeItem(index) {
			const value = _.cloneDeep(this.getCurrentFieldValue(this.name));

			value.splice(index, 1);

			this.updateAndValidate(value);
		},

		updateAndValidate(value) {
			this.updateFieldValue({ name: this.name, value });

			// TODO: change this...
			// I don't like that I'm forced to use $parent here
			Vue.nextTick(() => {
				this.$parent.validate()
			});
		},

		getError(fieldIndex, fieldName) {
			return (
				this.errors &&
				this.errors[fieldIndex] &&
				this.errors[fieldIndex][fieldName] ?
					this.errors[fieldIndex][fieldName] : null
			);
		}
	}
};
</script>
