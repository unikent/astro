<style>
.group-field {
	margin-bottom: 10px;
	padding: 10px;
}

.group-field__field-list {
	border: 1px solid #e8e8e8;
	background-color: #fff;
	padding: 20px 20px 40px;
	border-radius: 4px;
	position: relative;
}

.is-error .group-field {
	border: 1px solid #ff4949;
	border-radius: 4px;
}

.ci-field + .ci-field {
	border-top: 1px solid rgba(191, 203, 217, 0.3);
	margin-top: 24px;
	padding-top: 20px;
}

.group-field__field-list +
.group-field__field-list {
	margin-top: 24px;
}
</style>
<template>
<div class="group-field">

	<div class="group-field__field-list">
		<template v-for="f in field.fields">
			<div class="ci-field">
				<el-form-item
					:label="f.label"
					:prop="`${name}.${f.name}`"
					:rules="rules[name].fields[f.name]"
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
						:name="`${name}.${f.name}`"
						:index="currentIndex"
						:key="`${name}-${f.name}-${currentIndex}`"
					/>
				</el-form-item>
			</div>
		</template>
	</div>
</div>
</template>

<script>
import _ from 'lodash';
import { mapState } from 'vuex';
import baseFieldMixin from 'mixins/baseFieldMixin';
import getFieldMixin from 'mixins/getFieldMixin';
import { Definition } from 'classes/helpers';

export default {

	name: 'group-field',
	mixins: [baseFieldMixin, getFieldMixin],

	computed: {
		...mapState({
			currentIndex: state => state.page.currentBlockIndex,
			currentDefinition: state => state.definition.currentBlockDefinition
		}),

		rules() {
			return Definition.getRules(this.currentDefinition);
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
		}
	}
};
</script>