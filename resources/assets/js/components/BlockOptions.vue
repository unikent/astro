<template>
<div>
	<div class="b-back-button" @click="goBack">
		<i class="el-icon-arrow-left"></i>Back
	</div>
	<div class="block-options-list custom-scrollbar">
		<h2 v-if="currentDefinition">Edit {{ currentDefinition.label }}</h2>

		<div v-if="currentDefinition">
			<div v-for="field in currentDefinition.fields">
				<h3 class="field-label">
					{{ field.label }}
					<el-tooltip v-if="field.info" :content="field.info" placement="top">
						<icon
							class="field-info"
							:glyph="helpIcon"
							width="15"
							height="15"
							viewBox="0 0 15 15"
						/>
					</el-tooltip>
				</h3>
				<component
					:is="getField(field.type)"
					:field="field"
					:name="field.name"
					:index="currentIndex"
					:key="`${currentDefinition.name}-${currentIndex}`"
				/>
			</div>

			<el-button type="danger" @click="deleteThisBlock">Remove</el-button>

		</div>

	</div>
</div>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import fields from './fields';

import Icon from './Icon';
import helpIcon from 'IconPath/help.svg';

export default {

	components: {
		Icon
	},

	computed: {
		...mapGetters([
			'getCurrentBlock'
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
			'deleteBlock'
		]),

		goBack() {
			this.setBlock();
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
		}
	}

};
</script>