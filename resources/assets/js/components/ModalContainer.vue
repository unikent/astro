<template>
<div>
	<block-picker />
	<publish-modal />
</div>
</template>

<script>
import BlockPicker from 'components/BlockPicker';
import PublishModal from 'components/PublishModal';

export default {

	name: 'modal-container',

	components: {
		BlockPicker,
		PublishModal
	}

};
</script>