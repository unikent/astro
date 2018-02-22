<template>
<div class="block-options options-visible" v-loading.lock="loading">
	<back-bar
		:title="definition && definition.label ? definition.label : title"
	/>
	<div class="block-options-list custom-scrollbar" ref="options-list">
		<block-form
			v-if="definition"
			label-position="top"
			:model="fields"
			:rules="rules"
		>
			<el-form-item
				v-for="fieldDefinition in definition.fields"
				:label="fieldDefinition.label"
				:prop="fieldDefinition.name"
				:error="
					typeof errors[fieldDefinition.name] === 'string' ?
						errors[fieldDefinition.name] :
						null
				"
				:key="fieldDefinition.name"
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
							width="15"
							height="15"
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
								width="14"
								height="14"
								viewBox="0 0 14 14"
							/>
						</span>
					</el-tooltip> -->

				</template>
				<component
					:is="getField(fieldDefinition.type)"
					:field="fieldDefinition"
					:path="fieldDefinition.name"
					:index="currentIndex"
					:key="`${definition.name}-${currentIndex}`"
					:currentDefinition="definition"
					:errors="
						typeof errors[fieldDefinition.name] !== 'string' ?
							errors[fieldDefinition.name] :
							null
					"
				/>
			</el-form-item>

		</block-form>

		<slot v-else />
	</div>
</div>

</template>
<script>
import { mapState } from 'vuex';
import { Definition } from 'classes/helpers';
import BackBar from './BackBar';
import fields from 'components/fields';
import Icon from './Icon';
import BlockForm from './BlockForm';
import { eventBus } from 'plugins/eventbus';
import swapParentField from 'helpers/swapParentField';

export default {

	name: 'edit-options',

	props: ['title', 'item'],

	components: {
		Icon,
		BackBar,
		BlockForm
	},

	inject: ['fieldType'],

	computed: {

		...mapState({
			globalErrors: state => state.errors
		}),

		loading() {
			return this.currentItem === null;
		},

		definition() {
			return null;
		},

		currentItem() {
			return null;
		},

		fields: {
			get() {
				return this.currentItem || {};
			},
			set() {}
		},

		errors() {
			return (
				this.globalErrors.blocks ?
					this.globalErrors.blocks[this.currentRegion][this.currentIndex].fields : {}
			);
		},

		rules() {
			return Definition.getRules(this.definition, false);
		},

		identifier() {
			return null;
		}
	},

	methods: {

		getField(type) {
			const field = swapParentField(fields[type], this.fieldType);

			return (
				field || {
					name: 'missing-field-type',
					inheritAttrs: false,
					template: `
						 <el-alert
							title="This field type does not exist"
							type="warning"
							show-icon
							:closable="false"
						/>
					`
				}
			);
		},

		viewField(fieldName) {
			eventBus.$emit('global:showField', {
				id: this.identifier,
				fieldName
			});
		}

	}

};
</script>
