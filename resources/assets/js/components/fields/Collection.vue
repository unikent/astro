<style>
.collection-field {
	margin-bottom: 10px;
	padding: 10px;
}

.collection-field__field-list {
	border: 1px solid #e8e8e8;
	background-color: #fff;
	padding: 20px 20px 40px;
	border-radius: 4px;
	position: relative;
}

.is-error .collection-field {
	border: 1px solid #ff4949;
	border-radius: 4px;
    background-color: rgba(255, 73, 73, .08);
}

.ci-field + .ci-field {
	border-top: 1px solid rgba(191, 203, 217, 0.3);
	margin-top: 24px;
	padding-top: 20px;
}

.collection-field__field-list +
.collection-field__field-list {
	margin-top: 24px;
}
</style>
<template>
<div class="collection-field">
	<div
		v-for="(val, index) in currentValue"
		class="collection-field__field-list"
	>
		<template v-for="f in fields">
			<div class="ci-field">
				<el-form-item
					:label="f.label"
					:prop="`${name}.${index}.${f.name}`"
					:rules="getRules(f.name)"
				>
					<template slot="label">
						<span>{{ f.label }}</span>
						<el-tooltip v-if="f.info" :content="f.info" placement="top">
							<icon
								class="f-info"
								:glyph="helpIcon"
								width="15"
								height="15"
								viewBox="0 0 15 15"
							/>
						</el-tooltip>
					</template>
					<component
						:is="getField(f.type)"
						:field="childField(f)"
						:name="`${name}.${index}.${f.name}`"
						:index="currentIndex"
						:key="`${name}-${f.name}-${currentIndex}`"
					/>
				</el-form-item>
			</div>
		</template>

		<div @click="removeItem(index)" class="block-overlay__delete">
			<Icon :glyph="deleteIcon" width="20" height="20" />
		</div>
	</div>

	<div style="clear: both; margin-top: 24px">
		<span style="font-size: .8rem; color: #48576a;">
			{{ currentValue.length }} item{{ currentValue.length === 1 ? '' : 's' }}
		</span>
		<el-button @click="addItem" style="float: right;">Add item</el-button>
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
import deleteIcon from 'IconPath/trash.svg';

export default {

	name: 'collection-field',
	mixins: [baseFieldMixin, getFieldMixin],

	components: {
		Icon
	},

	computed: {
		...mapState({
			currentIndex: state => state.page.currentBlockIndex,
			currentDefinition: state => state.definition.currentBlockDefinition
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

	created() {
		this.deleteIcon = deleteIcon;
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
			})
		}
	}
};
</script>