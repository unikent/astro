<template>
<div>
	<block-picker />
	<publish-modal />
	<media-picker />
	<media-overlay size="small" />
</div>
</template>

<script>
import BlockPicker from 'components/BlockPicker';
import PublishModal from 'components/PublishModal';
import MediaPicker from 'components/MediaPicker';
import MediaOverlay from 'components/media/MediaOverlay';

export default {

	name: 'modal-container',

	components: {
		BlockPicker,
		PublishModal,
		MediaPicker,
		MediaOverlay
	}

};
</script>