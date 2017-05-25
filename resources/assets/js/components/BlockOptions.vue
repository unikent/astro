<template>
<div>
	<div class="b-back-button" @click="goBack">
		<i class="el-icon-arrow-left"></i>Back
	</div>
	<div class="block-options-list custom-scrollbar">
		<h2 v-if="currentDefinition">Edit {{ currentDefinition.label }}</h2>

		<el-form
			v-if="currentDefinition"
			label-position="top"
			:model="blockFields"
			:rules="rules"
			ref="block_fields"
		>
			<div v-for="field in currentDefinition.fields">
				<el-form-item :label="field.label" :prop="field.name" :error="localErrors[field.name] || null">
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
			</div>

			<el-button @click="submitForm('block_fields')">Save</el-button>
			<el-button type="danger" @click="deleteThisBlock">Remove</el-button>

		</el-form>

	</div>
</div>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import { Definition } from 'classes/helpers';
import fields from './fields';

import Icon from './Icon';
import helpIcon from 'IconPath/help.svg';

export default {

	components: {
		Icon
	},

	computed: {
		blockFields: {
			get() {
				return this.getCurrentBlock().fields;
			},
			set() {}
		},

		localErrors() {
			return this.errors.blocks ?
				this.errors.blocks[this.currentRegion][this.currentIndex].fields : {};
		},

		rules() {
			return Definition.getRules(this.currentDefinition);
		},

		...mapGetters([
			'getCurrentBlock'
		]),

		...mapState([
			'errors'
		]),

		...mapState({
			currentIndex: state => state.page.currentBlockIndex,
			currentRegion: state => state.page.currentRegion
		}),

		currentDefinition() {
			return this.$store.state.definition.currentBlockDefinition;
		}
	},

	created() {
		this.helpIcon = helpIcon;
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
			return (
				fields[type] ?
				fields[type] :  {
					name: type,
					template: '<div>This field type does not exist</div>'
				}
			);
		},

		submitForm(formName) {
			this.$refs[formName].validate((valid) => {
				if(!valid) {
					this.$snackbar.open({ message: 'Validation errors'})
					return false;
				}
			});
		}
	}

};
</script>