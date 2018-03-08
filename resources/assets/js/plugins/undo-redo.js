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
		store.commit('setPage', JSON.parse(pageData));
		eventBus.$emit('block:hideOverlay', null);
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
