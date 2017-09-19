<script>
import Vue from 'vue';
import { Form } from 'element-ui';

export default Vue.component('el-block-form', {
	extends: Form,
	data() {
		return {
			extendedMode: ''
		}
	},
})
</script>