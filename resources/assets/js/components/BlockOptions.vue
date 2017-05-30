<template>
<div>
	<div class="b-back-button" @click="goBack">
		<i class="el-icon-arrow-left"></i>Back
		<span v-if="currentDefinition && currentDefinition.label" class="block-title">
			{{ currentDefinition.label }}
		</span>
	</div>
	<div ref="options-list" class="block-options-list custom-scrollbar">
		<el-form
			v-if="currentDefinition"
			label-position="top"
			:model="blockFields"
			:rules="rules"
			ref="block_fields"
		>
			<el-form-item
				v-for="field in currentDefinition.fields"
				:label="field.label"
				:prop="field.name"
				:error="localErrors[field.name] || null"
				:key="field.name"
			>
				<template slot="label">
					<span>{{ field.label }}</span>
					<el-tooltip v-if="field.info" :content="field.info" placement="top">
						<icon
							class="field-info"
							:glyph="helpIcon"
							width="15"
							height="15"
							viewBox="0 0 15 15"
						/>
					</el-tooltip>
				</template>
				<component
					:is="getField(field.type)"
					:field="field"
					:name="field.name"
					:index="currentIndex"
					:key="`${currentDefinition.name}-${currentIndex}`"
				/>
			</el-form-item>
		</el-form>

	</div>

	<div class="b-bottom-bar">
		<el-button :plain="true" type="danger" @click="deleteThisBlock">Remove</el-button>
		<el-button @click="submitForm('block_fields')">Save</el-button>
	</div>
</div>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import Vue from 'vue';
import { Definition, getTopOffset } from 'classes/helpers';
import fields from 'components/fields';
import containers from 'components/fields/containers';
import { heights } from 'classes/sass';

import Icon from './Icon';
import helpIcon from 'IconPath/help.svg';

/* global document */

export default {

	name: 'block-options',

	components: {
		Icon
	},

	computed: {
		blockFields: {
			get() {
				const currentBlock = this.getCurrentBlock();
				if(currentBlock) {
					return currentBlock.fields;
				}
			},
			set() {}
		},

		localErrors() {
			return this.errors.blocks ?
				this.errors.blocks[this.currentRegion][this.currentIndex].fields : {};
		},

		rules() {
			return Definition.getRules(this.currentDefinition, false);
		},

		...mapGetters([
			'getCurrentBlock'
		]),

		...mapState([
			'errors'
		]),

		...mapState({
			currentIndex: state => state.page.currentBlockIndex,
			currentRegion: state => state.page.currentRegion,
			currentBlock: state => {
				if(!state.page.pageData.blocks) {
					return null;
				}
				return state.page.pageData.blocks[state.page.currentRegion][state.page.currentBlockIndex];
			},

			currentDefinition: state => state.definition.currentBlockDefinition
		})
	},

	created() {
		this.helpIcon = helpIcon;
	},

	watch: {
		currentBlock(val, oldVal) {
			// if block changes scroll to top
			if(val && oldVal && val.id !== oldVal.id) {
				this.$refs['options-list'].scrollTop = 0;
			}
		}
	},

	methods: {
		...mapMutations([
			'setBlock',
			'deleteBlock',
			'updateErrors'
		]),

		goBack() {
			this.setBlock();
			this.updateErrors({});
		},

		deleteThisBlock() {
			this.deleteBlock();
			this.setBlock();
		},

		getField(type) {
			const field = fields[type] || containers[type];
			return (
				field || {
					name: type,
					template: '<div>This field type does not exist</div>'
				}
			);
		},

		submitForm(formName) {
			this.$refs[formName].validate((valid) => {
				if(!valid) {
					this.$snackbar.open({ message: 'Validation errors'});

					Vue.nextTick(() => {
						const firstError = document.querySelector('.block-options-list .is-error');

						if(firstError) {
							this.$refs['options-list'].scrollTop = (
								getTopOffset(firstError) -
								heights['top-bar'][0] -
								heights['block-back-bar'][0] - 20
							);
						}
					});

					return false;
				}
			});
		}
	}

};
</script>