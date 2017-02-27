import Vue from 'vue';
import Vuex from 'vuex';
import page from '../stubs/page';
import undoRedo from './undo-redo';
import { Message } from 'element-ui';
import api from '../libs/api';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const store = (
	window.self === window.top ?

		(window.blocksStore = new Vuex.Store({

			state: {
				blockIndex: null,
				blockDef: null,
				blockList: null,
				page
			},

			getters: {

				getFieldValue: (state, getters) => (index, name) => {
					return state.page.blocks[index].fields[name];
				},

				getCurrentFieldValue: (state, getters) => (name) => {
					return getters.getFieldValue(state.blockIndex, name);
				}

			},

			mutations: {
				setBlockList(state, list) {
					state.blockList = list;
				},

				editBlock(state, { index, type } = { index: null, type: null }) {
					state.blockIndex = state.blockIndex ? null : index;
					state.blockDef = state.blockDef ? null : state.blockList[type];
				},

				updateValue(state, { name, value }) {
					const block = state.page.blocks[state.blockIndex];
					block.fields[name] = value;
					state.page.blocks.splice(state.blockIndex, 1, block);
				}
			},

			actions: {
				fetchBlockList({ commit }) {
					api
						.get('definition')
						.then((response) => {
							commit('setBlockList', response.data);
						});
				},

				editBlock({ commit }, payload) {
					commit('editBlock', payload);
				},

				updateValue({ commit }, data) {
					commit('updateValue', data);
				}

				// TODO: add these actions?
				// addRow(row, index) // uuid.v1?
				// updateRows(index, row)
				// deleteRow(row, index)
				// sortRows(to, from)
			},

			strict: debug,

			plugins: [undoRedo]

		})) :

		window.top.blocksStore
);

window.store = store;

export default store;