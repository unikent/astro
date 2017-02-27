const undoRedo = store => {
	store.subscribe((mutation, state) => {
		// TODO: figure out logic for what mutations can be reverted
		// console.log(mutation.type, mutation.payload, window.self === window.top ? 'top window' : 'iframe window');
	})
}

export default undoRedo;