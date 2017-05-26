const state = {
	currentBlockDefinition: null,
	blockDefinitions: {}
};

const getters = {};

const actions = {};

const mutations = {

	setBlockDefinitions(state, list) {
		state.blockDefinitions = list;
	},

	setBlock(state, { type } = { type: null }) {
		state.currentBlockDefinition = state.blockDefinitions[type];
	}
};

export default {
	state,
	getters,
	actions,
	mutations
};
