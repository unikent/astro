<template>
<div class="block-options options-visible" v-loading.lock="loading">
	<back-bar
		:title="definition && definition.label ? definition.label : title"
	/>
	<div class="block-options-list-scroll custom-scrollbar" ref="options-list">
		<div class="block-options-list">
			<el-form
				v-if="definition && definition.fields.length"
				label-position="top"
				:model="model"
				:rules="rules"
			>
				<el-form-item
					v-for="fieldDefinition in definition.fields"
					:error="getErrors(fieldDefinition.name)"
					:class="{
						'is-required': isRequiredField(fieldDefinition)
					}"
					:id="fieldDefinition.name"
					:key="`${fieldDefinition.name}_${identifier}`"
					:prop="fieldDefinition.name"
				>
					<template slot="label" v-if="fieldDefinition.label">
						<span>{{ fieldDefinition.label }}</span>

						<el-tooltip
							v-if="fieldDefinition.info"
							popper-class="el-tooltip__popper--narrow"
							:content="fieldDefinition.info"
							placement="top"
						>
							<icon
								class="el-form-item__icon-help"
								name="help-circle"
								:width="15"
								:height="15"
								viewBox="0 0 15 15"
							/>
						</el-tooltip>

						<!-- <el-tooltip content="Highlight field" placement="top">
							<span
								class="el-form-item__icon-view"
								@click="viewField(fieldDefinition.name)"
							>
								<icon
									name="eye"
									:width="14"
									:height="14"
									viewBox="0 0 14 14"
								/>
							</span>
						</el-tooltip> -->
					</template>
					<component
						:is="getField(fieldDefinition.type)"
						:field="fieldDefinition"
						:path="fieldDefinition.name"
					/>
				</el-form-item>
			</el-form>

			<slot v-else>
				There are currently no options available.
			</slot>
		</div>
	</div>
</div>

</template>
<script>
import { mapGetters } from 'vuex';

import BackBar from './BackBar';
import fields from 'components/fields';
import Icon from './Icon';
import swapParentField from 'helpers/swapParentField';
import { heights } from 'classes/sass';

export default {

	name: 'edit-options',

	props: ['title', 'item'],

	components: {
		Icon,
		BackBar
	},

	inject: ['fieldType'],

	computed: {
		loading() {
			return this.currentItem === null;
		},

		definition() {
			return null;
		},

		currentItem() {
			return null;
		},

		model() {
			return null;
		},

		errors() {
			return {};
		},

		rules() {
			return null;
		},

		identifier() {
			return null;
		},

		fields() {
			let f = {};

			Object
				.keys(fields)
				.forEach(
					type => f[type] = swapParentField(fields[type], this.fieldType)
				);

			return f;
		}
	},

	mounted() {
		this.$bus.$on('error-sidebar:scroll-to-error', this.scrollTo);
	},

	beforeDestroy() {
		this.$bus.$off('error-sidebar:scroll-to-error', this.scrollTo);
	},

	methods: {

		getField(type) {
			return (
				this.fields[type] || {
					name: 'missing-field-type',
					inheritAttrs: false,
					template: `
						<el-alert
							title="This field type does not exist (${type})"
							type="warning"
							show-icon
							:closable="false"
						/>
					`
				}
			);
		},

		viewField(fieldName) {
			this.$bus.$emit('global:showField', {
				id: this.identifier,
				fieldName
			});
		},

		getErrors(fieldName) {
			return null;
		},

		isRequiredField(definition) {
			return definition.validation && definition.validation.includes('required');
		},

		scrollTo({ fieldPath }) {
			if(!fieldPath) {
				return;
			}

			const
				optionsList = this.$refs['options-list'],
				fieldOffset = (
					optionsList
						.querySelector(`#${fieldPath.replace(/\./g, '-')}`)
						.getBoundingClientRect().top
				),
				offset = heights['top-bar'][0] + heights['block-back-bar'][0];

			optionsList.scrollTop = optionsList.scrollTop + fieldOffset - offset;
		}
	}

};
</script>
