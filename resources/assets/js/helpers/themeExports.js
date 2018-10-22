import _ from 'lodash';

import {
	layouts,
	blocks,
	fieldParentTypes as externalfieldParentTypes,
	fields,
	routes as externalRoutes,
	stores
} from '@theme';

const addThemeRoutes = (routes) => {
	Object.keys(externalRoutes).forEach(insertionPath => {
		// if a route exists at this path already insert after it
		if(_.has(routes, insertionPath)) {
			const
				subRoutePath = insertionPath.split('.').pop(),
				route = _.get(routes, insertionPath);

			route.splice(parseInt(subRoutePath), 0, externalRoutes[insertionPath]);
		}
		// otherwise just set the sucker
		else {
			_.set(routes, insertionPath, externalRoutes[insertionPath]);
		}
	});

	return routes;
};

const addThemeStoreModules = (modules) => {
	return {
		...modules,
		...stores
	};
};

const addThemeFieldParentTypes = (fieldParentTypes) => {
	return {
		...fieldParentTypes,
		...externalfieldParentTypes
	};
};

export {
	layouts,
	blocks,
	fields,
	addThemeRoutes,
	addThemeStoreModules,
	addThemeFieldParentTypes
};
