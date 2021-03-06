import { win, isIframe } from 'classes/helpers';
import { eventBus } from './eventbus';
import UndoStack from 'classes/UndoStack';

const trackedMutations = {
	updateFieldValue: 'Update to block field',
	addBlock: 'Added block to page',
	reorderBlocks: 'Reordered blocks on page',
	deleteBlock: 'Deleted block on page'
};

const undoStack = new UndoStack({ lock: true });

const undoRedo = store => {
	if(isIframe) {
		return;
	}

	undoStack.setUndoRedo(pageData => {
		const page = JSON.parse(pageData);
		store.commit('setPage', page);
		store.dispatch('initialiseBlocksAndValidate', page.blocks);
		eventBus.$emit('block:hideHoverOverlay');
		eventBus.$emit('block:updateBlockOverlays');
	});

	undoStack.setCallback(({ canUndo, canRedo }) => {
		store.commit('updateUndoRedo', { canUndo, canRedo });
	});

	store.subscribe((mutation, state) => {
		if(!trackedMutations[mutation.type]) {
			return;
		}

		undoStack.add(state.page.pageData);
	});
};

export default undoRedo;

export const undoStackInstance = isIframe ?
	win.top.astroUndoStack : (win.astroUndoStack = undoStack);
