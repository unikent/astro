import _ from 'lodash';

export const getPathsToItem = (items, matcher, exclude = [], path = [], previous = []) => {

	_.forEach(items, (value, keyOrIndex) => {
		if(!exclude.includes(keyOrIndex)) {
			if(matcher(value, keyOrIndex)) {
				path.push([...previous, keyOrIndex]);
			}
			else if(_.isObject(value)) {
				getPathsToItem(
					value,
					matcher,
					exclude,
					path,
					[...previous, keyOrIndex]
				);
			}
		}
	});

	return path;
}
