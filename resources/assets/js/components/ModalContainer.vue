<template>
<div>
	<block-picker class="el-dialog--large" />
	<publish-modal />
	<unpublish-modal />
	<media-picker />
	<media-overlay />
	<create-page-modal />
	<edit-page-modal/>
	<publish-validation-warning-modal />
	<add-link-modal />
</div>
</template>

<script>

/**
 * Contains modal dialogs which need to cover the whole screen (both the main Editor frame and the Preview iframe).
 */

import BlockPicker from 'components/BlockPicker';
import PublishModal from 'components/PublishModal';
import UnpublishModal from 'components/UnpublishModal';
import MediaPicker from 'components/MediaPicker';
import MediaOverlay from 'components/media/MediaOverlay';
import CreatePageModal from 'components/CreatePageModal';
import EditPageModal from 'components/EditPageModal';
import PublishValidationWarningModal from 'components/PublishValidationWarningModal';
import AddLinkModal from 'components/AddLinkModal';

export default {

	name: 'modal-container',

	components: {
		BlockPicker,
		PublishModal,
		UnpublishModal,
		MediaPicker,
		MediaOverlay,
		CreatePageModal,
		EditPageModal,
		PublishValidationWarningModal,
		AddLinkModal
	}

};
</script>
