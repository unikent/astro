<script>
/**
custom form component for use with the block editor which reports its current validation 
state to the vuex store

inherits from the standard Element Form http://element.eleme.io/#/en-US/component/form
**/
import Vue from 'vue';
import { Form } from 'element-ui';

export default Vue.component('el-block-form', {
	extends: Form,
	
	computed: {
		valid: function() {
			let invalidElements = this.fields.filter(function (value) {
				return value.validateState == 'error';
			});
			return invalidElements.length == 0;
			// return invalidElements;
		}
	},

	watch: {

	},

	data() {
		return {
			
		}
	},
})
</script>