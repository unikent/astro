/**
store for validation issues with the currently edited page
*/
import Vue from 'vue';

const state = {
	invalidBlocks: new Set(),
}

// actions

// populate the errors on load

// gettters

// mutations
const mutations = {

	addBlockValidationIssue(state, block_id) {
		console.log('adding issue for ' + block_id);
		state.invalidBlocks.add(block_id);
		console.log(state.invalidBlocks.size);
	},

	deleteBlockValidationIssue(state, block_id) {
		console.log('removing validation issue ' + block_id);
		state.invalidBlocks.delete(block_id);
		console.log(state.invalidBlocks.size);
	}

	// something to populate this on page load

	// something to clear all issues
};


export default {
	state,
	// actions,
	mutations,
	// getters
};
