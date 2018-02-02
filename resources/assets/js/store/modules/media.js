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
	}
};

const getters = {};

export default {
	state,
	actions,
	mutations,
	getters
};
