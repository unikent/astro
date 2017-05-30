const state = {
	currentBlockIndex: null,
	currentBlockDefinition: null,
	blockDefinitions: {}
};

const getters = {};

const actions = {};

const mutations = {

	setBlockDefinitions(state, list) {
		state.blockDefinitions = list;
	},

	// TODO: move to shared action?
	setBlock(state, { index, type } = { index: null, type: null }) {
		state.currentBlockIndex = index;
		state.currentBlockDefinition = state.blockDefinitions[type];
	},

	deleteBlock(state, { index } = { index: null }) {
		if(index !== null && index === state.currentBlockIndex) {
			state.currentBlockDefinition = null;
			state.currentBlockIndex = null;
		}
	}
};

export default {
	state,
	getters,
	actions,
	mutations
};
