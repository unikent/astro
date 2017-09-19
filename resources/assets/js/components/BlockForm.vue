<script>
/**
custom form component for use with the block editor which reports its current validation 
state to the vuex store

inherits from the standard Element Form
**/
import { mapMutations } from 'vuex';
import Vue from 'vue';
import { Form } from 'element-ui';

export default Vue.component('el-block-form', {
	extends: Form,

	data: function () {
	  return {
	    currentValidationState: true
	  }
	},

	methods: {
		...mapMutations([
			'removeValidationIssue', 
			'addValidationIssue'
			]),
	},
	computed: {
		valid: function() {
			if (this.fields) {
				for (var i = this.fields.length - 1; i >= 0; i--) {
					if (this.fields[i].validateState == 'error') {
						return false;
					}
				}			
			}
			return true;	
		}
	},
	watch:{
		valid: function(value) {
			if (value) {
				this.removeValidationIssue();
			} else {
				this.addValidationIssue();
			}
		}	
	}	

})
</script>