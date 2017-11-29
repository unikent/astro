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
				v-for="fieldDefinition in currentDefinition.fields"
				:label="fieldDefinition.label"
				:prop="fieldDefinition.name"
				:error="
					typeof localErrors[fieldDefinition.name] === 'string' ?
						localErrors[fieldDefinition.name] :
						null
				"
				:key="fieldDefinition.name"
			>
				<template slot="label">
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

					<!--
					<el-tooltip content="Highlight field" placement="top">
						<icon
							class="el-form-item__icon-view"
							name="eye"
							width="14"
							height="14"
							viewBox="0 0 14 14"
							@click="viewField(fieldDefinition.name)"
						/>
					</el-tooltip>
					-->
				</template>
				<component
					:is="getField(fieldDefinition.type)"
					:field="fieldDefinition"
					:path="fieldDefinition.name"
					:index="currentIndex"
					:key="`${currentDefinition.name}-${currentIndex}`"
					:scrollTo="scrollTo"
					:currentDefinition="currentDefinition"
					:errors="
						typeof localErrors[fieldDefinition.name] !== 'string' ?
							localErrors[fieldDefinition.name] :
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
import { Definition, getTopOffset } from 'classes/helpers';
import BackBar from './BackBar';
import fields from 'components/fields';
import containers from 'components/fields/containers';
import { heights } from 'classes/sass';
import Icon from './Icon';
import BlockForm from './BlockForm';

/* global document, console */
/* eslint-disable no-console */

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
			'currentBlock',
			'currentDefinition'
		]),

		...mapState([
			'errors'
		]),

		...mapState({
			currentIndex: state => state.contenteditor.currentBlockIndex,
			currentRegion: state => state.contenteditor.currentRegionName
		}),

		mode() {
			return this.currentDefinition ? 'edit' : 'list';
		},

		blockFields: {
			get() {
				if(this.currentBlock) {
					return this.currentBlock.fields;
				}
			},
			set() {}
		},

		localErrors() {
			return {};
			// return this.errors.blocks ?
			// 	this.errors.blocks[this.currentRegion][this.currentIndex].fields : {};
		},

		// TODO: move validation outside of element
		// This component used to hang briefly while the validation rules were
		// being transformed... this now happens when the definitions are first
		// loaded and the rules are cached.
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
			// 	smoothScrollTo() // from helpers
			// });
			console.log(el);
		},

		submitForm(formName) {
			this.$refs[formName].validate((valid) => {
				if(!valid) {
					this.$snackbar.open({ message: 'Validation errors' });

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
