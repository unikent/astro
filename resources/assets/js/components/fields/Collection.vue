<template>
<div class="collection-field">
	<transition-group name="flip-list">
		<div
			v-for="(val, index) in currentValue"
			class="collection-field__field-list"
			:key="keys[index]"
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

			<div class="collection__modify-buttons">
				<el-button-group>
					<el-tooltip :content="`Move this ${friendlyLabel} up`" :open-delay="400">
						<el-button
							@click="moveItem(index, -1)"
							:disabled="index === 0"
							size="small"
						>
							<i class="el-icon-arrow-up el-icon--left"></i>
						</el-button>
					</el-tooltip>
					<el-tooltip :content="`Move this ${friendlyLabel} down`" :open-delay="400">
						<el-button
							@click="moveItem(index, 1)"
							:disabled="index === currentValue.length - 1"
							size="small"
						>
							<i class="el-icon-arrow-down el-icon--right"></i>
						</el-button>
					</el-tooltip>
				</el-button-group>

				<el-tooltip :content="`Remove this ${friendlyLabel}`" :open-delay="400">
					<el-button
						plain
						type="danger"
						size="small"
						@click="removeItem(index)"
					>
						<icon name="delete" width="15" height="15" />
					</el-button>
				</el-tooltip>
			</div>
		</div>
	</transition-group>

	<div class="collection-add">
		<span class="collection-add__counter">
			{{ currentValue.length }} {{ friendlyLabel }}{{ currentValue.length === 1 ? '' : 's' }}
		</span>
		<el-button @click="addItem" class="collection-add__button">Add {{ friendlyLabel }}</el-button>
	</div>
</div>
</template>

<script>
import _ from 'lodash';
import Vue from 'vue';
import { mapState, mapGetters } from 'vuex';
import BlockField from 'components/BlockField';
import getFieldMixin from 'mixins/getFieldMixin';
import { Definition } from 'classes/helpers';

import Icon from '../Icon';

export default {

	name: 'collection-field',

	extends: BlockField,

	mixins: [getFieldMixin],

	components: {
		Icon
	},

	created() {
		let length = this.currentValue.length;
		this.incr = 0;

		while(length--) {
			this.keys.push(this.incr++);
		}
	},

	data() {
		return {
			keys: []
		};
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
		},

		friendlyLabel() {
			return this.label.slice(0, -1).toLowerCase();
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
			this.keys.push(this.incr++);

			this.updateAndValidate(value);
		},

		moveItem(index, num) {
			const value = _.cloneDeep(this.getCurrentFieldValue(this.name));

			value.splice(index + num, 0, value.splice(index, 1)[0]);
			this.keys.splice(index + num, 0, this.keys.splice(index, 1)[0]);

			this.updateAndValidate(value);
		},

		removeItem(index) {
			const value = _.cloneDeep(this.getCurrentFieldValue(this.name));

			value.splice(index, 1);
			this.keys.splice(index, 1);

			this.updateAndValidate(value);
		},

		updateAndValidate(value) {
			this.updateFieldValue(this.name, value);

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
