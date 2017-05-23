import _ from 'lodash';
import { isIframe, debug, win } from 'classes/helpers';

const syncMutation = (sync, lock, store, mutation) => {
	// lock iframe/top vuex from syncing
	sync[lock] = true;

	// actually sync the mutation
	sync[store].commit(
		mutation.type,
		_.cloneDeep(mutation.payload)
	);

	// unlock
	sync[lock] = false;
};

export const shareMutationsMain = store => {
	let sync = win.astroSync = {};

	sync.iframeIsSyncing = false;
	sync.parentIsSyncing = false;

	sync.store = store;

	store.subscribe(mutation => {
		if(!sync.iframeStore) {
			return;
		}

		if(!sync.iframeIsSyncing) {
			debug(`[Syncing top -> iframe] ${mutation.type} mutation`);

			syncMutation(
				sync,
				'parentIsSyncing',
				'iframeStore',
				mutation
			);
		}
	});
};

export const shareMutationsIframe = store => {
	let sync = win.top.astroSync;

	sync.iframeStore = store;

	// copy current top window state into iframe state
	store.replaceState(_.cloneDeep(sync.store.state));

	store.subscribe(mutation => {
		if(!sync.store) {
			return;
		}

		if(!sync.parentIsSyncing) {
			debug(`[Syncing iframe -> top] ${mutation.type} mutation`);

			syncMutation(
				sync,
				'iframeIsSyncing',
				'store',
				mutation
			);
		}
	});
};

export default isIframe ? shareMutationsIframe : shareMutationsMain;
