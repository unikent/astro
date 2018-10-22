import { MessageBox, Message } from 'element-ui';
import api from 'plugins/http/api';
import { eventBus } from 'plugins/eventbus';

const state = {
	mediaPicker: {
		visible: false,
		fieldPath: null,
		mediaType: 'media'
	},
	mediaOverlayVisible: false,
	mediaItem: {}
};

const mutations = {
	showMediaPicker(state) {
		state.mediaPicker.visible = true;
	},

	hideMediaPicker(state) {
		state.mediaPicker.visible = false;
	},

	setMediaType(state, type) {
		state.mediaPicker.mediaType = type;
	},

	updateMediafieldPath(state, path) {
		state.mediaPicker.fieldPath = path;
	},

	setMediaOverlayVisibility(state, visible) {
		state.mediaOverlayVisible = visible;
	},

	setMediaItem(state, value) {
		state.mediaItem = value;
	}
};

const actions = {
	showMediaOverlay({ commit }, item) {
		commit('setMediaItem', item);
		commit('setMediaOverlayVisibility', true);
	},

	hideMediaOverlay({ commit }) {
		commit('setMediaOverlayVisibility', false);
	},

	detachMediaFromSite({ rootState }, item) {
		const siteId = rootState.site.site;

		MessageBox.confirm(
			'Are you sure you want to delete this file?',
			'Deleting media from site',
			{
				confirmButtonText: 'OK',
				cancelButtonText: 'Cancel',
				type: 'warning'
			}
		)
		.then(() => {
			api
			.delete(`/sites/${siteId}/media/${item.id}`)
			.then(() => {
				eventBus.$emit('media:refresh');
				Message({
					type: 'success',
					message: 'File successfully deleted.'
				});
			})
			.catch(() => {
				Message({
					type: 'error',
					message: 'Unable to delete this file.'
				});
			});
		})
		.catch(() => {});
	}
};

const getters = {};

export default {
	state,
	actions,
	mutations,
	getters
};
