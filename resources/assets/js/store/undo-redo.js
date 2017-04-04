const undoStack = [];
let timeout = null;

const undoRedo = store => {
	store.subscribe((mutation, state) => {
		// TODO: figure out logic for what mutations can be reverted
		// and ideally do so in a clever manner (by grouping edits together)
		// clearTimeout(timeout);

		// timeout = setTimeout(() => {
		// 	undoStack.push({
		// 		...mutation
		// 	});
		// 	console.log(undoStack);
		// }, 1000);

		// console.log(mutation.type, mutation.payload, window.self === window.top ? 'top window' : 'iframe window');
	})
}

export default undoRedo;