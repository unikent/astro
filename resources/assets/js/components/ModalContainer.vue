<template>
<div>
	<block-picker />
	<publish-modal />
	<media-picker />
	<media-overlay size="small" />
	<create-page-modal />
	<edit-page-modal/>
	<publish-validation-warning-modal />
</div>
</template>

<script>
import BlockPicker from 'components/BlockPicker';
import PublishModal from 'components/PublishModal';
import MediaPicker from 'components/MediaPicker';
import MediaOverlay from 'components/media/MediaOverlay';
import CreatePageModal from 'components/CreatePageModal';
import EditPageModal from 'components/EditPageModal';
import PublishValidationWarningModal from 'components/PublishValidationWarningModal';

export default {

	name: 'modal-container',

	components: {
		BlockPicker,
		PublishModal,
		MediaPicker,
		MediaOverlay,
		CreatePageModal,
		EditPageModal,
		PublishValidationWarningModal
	}

};
</script>
