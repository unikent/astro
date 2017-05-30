import _ from 'lodash';
import { isIframe, win } from 'classes/helpers';

const parentDevtoolHook = isIframe && win.top.__VUE_DEVTOOLS_GLOBAL_HOOK__;

export default (store) => {
	if(!parentDevtoolHook) {
		return;
	}

	parentDevtoolHook.on('vuex:travel-to-state', targetState => {
		store.replaceState(_.cloneDeep(targetState))
	});
}
