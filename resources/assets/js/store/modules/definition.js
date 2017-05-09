import definitions from '../../tests/stubs/definitions.json';
// import api from '../../plugins/http/api';

const state = {
	blockList: null,
	currentBlockDefinition: null
};

const getters = {};

const actions = {

	fetchBlockList({ commit }) {
		// api
		// 	.get('block/definitions')
		// 	.then((response) => {
		// 		commit('setBlocklist', response.data.data);
		// 		console.log(response.data.data);
		// 	});

		let defs = {};

		Object.keys(definitions).forEach(
			name => {
				const def = definitions[name];

				if(def) {
					defs[`${def.name}-v${def.version}`] = def;
				}
			}
		);

		commit('setBlocklist', defs);
	}
};

const mutations = {

	setBlocklist(state, list) {
		state.blockList = list;
	},

	setBlock(state, { type } = { type: null }) {
		state.currentBlockDefinition = state.blockList[type] || null;
	}
};

export default {
	state,
	getters,
	actions,
	mutations
};
