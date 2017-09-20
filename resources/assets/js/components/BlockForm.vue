<script>
/**
custom form component for use with the block editor which reports its current validation 
sends a passValidation or failValidation event to its parent 
*/
import Vue from 'vue';
import { Form } from 'element-ui';

export default Vue.component('block-form', {
	extends: Form,

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
		valid: function(isValid) {
			if (isValid) {
				this.$emit('passValidation');
			} else {
				this.$emit('failValidation');
			}
		}	
	},
	updated: function() {
		// invoked when the form is loaded with a new block of data
		this.validate((isValid) => {
			if (isValid) {
				this.$emit('passValidation');
			} else {
				this.$emit('failValidation');
			}
		});

	}
})
</script>