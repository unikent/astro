<template>
<icon class="media-item__icon" name="sites" width="100%" height="100%" />
</template>

<script>
import Icon from 'components/Icon';

export default {

	props: {
		type: {
			type: String,
			default: 'document'
		}
	},

	components: {
		Icon
	}
};
</script>