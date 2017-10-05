<template>
<div class="block-options" :class="{'options-visible' : mode === 'edit'}">
	<back-bar
		:title="
			currentDefinition && currentDefinition.label ?
				currentDefinition.label : title
		"
	 />
	<div ref="options-list" id="block-options-list" class="block-options-list custom-scrollbar">
		<block-form
			v-if="currentDefinition"
			v-on:failValidation="setValidation(false)"
			v-on:passValidation="setValidation(true)"
			label-position="top"
			:model="blockFields"
			:rules="rules"
			ref="block_fields"
		>
			<el-form-item
				v-for="field in currentDefinition.fields"
				:label="field.label"
				:prop="field.name"
				:error="
					typeof localErrors[field.name] === 'string' ?
						localErrors[field.name] :
						null
				"
				:key="field.name"
			>
				<template slot="label">
					<span>{{ field.label }}</span>

					<el-tooltip
						v-if="field.info"
						popper-class="el-tooltip__popper--narrow"
						:content="field.info"
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

					<!--
					<el-tooltip content="Highlight field" placement="top">
						<icon
							class="el-form-item__icon-view"
							name="eye"
							width="14"
							height="14"
							viewBox="0 0 14 14"
							@click="viewField(field.name)"
						/>
					</el-tooltip>
					-->
				</template>
				<component
					:is="getField(field.type)"
					:field="field"
					:path="field.name"
					:index="currentIndex"
					:key="`${currentDefinition.name}-${currentIndex}`"
					:scrollTo="scrollTo"
					:errors="
						typeof localErrors[field.name] !== 'string' ?
							localErrors[field.name] :
							null
					"
				/>
			</el-form-item>
		</block-form>

		<!-- TODO: make this look nice -->
		<div v-else>Click a block to display its options in this sidebar.</div>
	</div>

	<!-- hide until we know what we're doing with validation -->
	<div class="b-bottom-bar" v-show="false">
		<el-button :plain="true" type="danger" @click="deleteThisBlock">Remove</el-button>
		<el-button @click="submitForm('block_fields')">Validate</el-button>
	</div>
</div>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import Vue from 'vue';
import { Definition, getTopOffset, smoothScrollTo } from 'classes/helpers';
import BackBar from './BackBar';
import fields from 'components/fields';
import containers from 'components/fields/containers';
import { heights } from 'classes/sass';
import Icon from './Icon';
import BlockForm from './BlockForm';

/* global document */

export default {

	name: 'block-options',

	props: ['title'],

	components: {
		Icon,
		BackBar,
		BlockForm
	},

	computed: {
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
		}),

		mode() {
			return this.currentDefinition ? 'edit' : 'list';
		},

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

		// @TODO - this lags when switching blocks with different rule sets- investigate
		rules() {
			return Definition.getRules(this.currentDefinition, false);
		}
	},

	watch: {
		currentBlock(val, oldVal) {
			// if block is removed hide sidebar
			if(val === void 0) {
				this.setBlock();
			}
			// if block changes scroll to top
			if(val && oldVal && val.id !== oldVal.id) {
				this.$refs['options-list'].scrollTop = 0;
			}
		},
	},

	methods: {
		...mapMutations([
			'setBlock',
			'deleteBlock',
			'updateErrors',
			'addBlockValidationIssue',
			'deleteBlockValidationIssue'
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

		scrollTo(el) {
			// Vue.nextTick(() => {
			// 	smoothScrollTo()
			// });
			console.log(el);
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
		},

		viewField(fieldName) {
			console.log(this.currentBlock, fieldName);
			if(this.currentBlock.fieldElements[fieldName]) {
				console.log(this.currentBlock.fieldElements[fieldName]);
			}
		},

		setValidation(status) {
			if(status) {
				this.deleteBlockValidationIssue(this.currentBlock.id);
			}
			else {
				this.addBlockValidationIssue(this.currentBlock.id);
			}
		}

	}

};
</script>
