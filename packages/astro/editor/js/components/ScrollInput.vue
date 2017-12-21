<script>
/*
 * This scrolls input elements when they are mounted to reveal the last bit
 * of text. This is especially useful for differentiating URLs, which was the
 * case with MenuEditor.vue (where this is used).
 */
import { Input } from 'element-ui';

export default {
	name: 'scroll-el-input',

	extends: Input,

	mounted() {
		const input = this.$refs['input'];
		input.scrollLeft = input.scrollWidth;
	}
};
</script>