<template>
<div class="group-field">

	<div class="group-field__field-list">
		<template v-for="f in field.fields">
			<div class="ci-field">
				<el-form-item
					:label="f.label"
					:prop="`${name}.${f.name}`"
					:rules="getRules(f.name)"
					:error="getErrors(`${field.name}.${f.name}`)"
					:class="{
						'is-required': isRequiredField(f)
					}"
					:id="`${name}-${f.name}`"
				>
					<template slot="label">
						<span>{{ f.label }}</span>
						<span class="el-form-item__help" v-html="f.info"></span>
					</template>

					<component
						:is="getField(f.type)"
						:field="childField(f)"
						:path="`${name}.${f.name}`"
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
import BlockField from 'components/BlockField';
import getFieldMixin from 'mixins/getFieldMixin';
import { Definition } from 'classes/helpers';
import Icon from '../Icon';

export default {

	name: 'group-field',

	extends: BlockField,

	mixins: [getFieldMixin],

	components: {
		Icon
	},

	computed: {
		...mapState({
			currentIndex: state => state.page.currentBlockIndex
		})
	},

	methods: {

		childField(field) {
			return _.pick(field, ['label', 'default', 'options']);
		}

	}
};
</script>
