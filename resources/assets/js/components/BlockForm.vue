<script>
/**
 * Custom form component for use with the block editor which reports its current validation
 * Sends a block:setValidation event to the eventBus which can be optionally listened for.
 * This is listened to by EditBlock for tracking block validation.
 *
 * Used within the editor sidebar to allow users to update blocks.
 *
 * @extends Element UI Form component
 */
import { Form } from 'element-ui';
import { eventBus } from 'plugins/eventbus';

export default {
	name: 'block-form',

	extends: Form,

	computed: {
		valid() {
			if(this.fields) {
				for (var i = this.fields.length - 1; i >= 0; i--) {
					if (this.fields[i].validateState === 'error') {
						return false;
					}
				}
			}
			return true;
		}
	},

	watch: {
		valid(status) {
			eventBus.$emit('block:setValidation', status);
		}
	},

	updated() {
		// invoked when the form is loaded with a new block of data
		this.validate((status) => {
			eventBus.$emit('block:failValidation', status);	
		});
	}
};
</script>