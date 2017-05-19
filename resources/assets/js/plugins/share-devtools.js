import _ from 'lodash';
import { isIframe } from 'classes/helpers';

/* global window */

const parentDevtoolHook = isIframe && window.top.__VUE_DEVTOOLS_GLOBAL_HOOK__;

export default (store) => {
	if(!parentDevtoolHook) {
		return;
	}

	parentDevtoolHook.on('vuex:travel-to-state', targetState => {
		store.replaceState(_.cloneDeep(targetState))
	});
}
