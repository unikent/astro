/* global clearTimeout, setTimeout */

export default class UndoStack {

	defaultOptions = {
		maxSize: 30,
		lock: false,
		undoGroupingInterval: 400,
		onUndoRedo: () => {},
		callback: () => {}
	};

	constructor(opts = {}) {
		opts = { ...this.defaultOptions, ...opts };
		this.stack = [];
		this.index = -1;
		this.timer = null;

		this.maxSize = opts.maxSize;
		this.undoGroupingInterval = opts.undoGroupingInterval;
		this.isLocked = opts.lock;
		this.onUndoRedo = opts.onUndoRedo;
		this.callback = opts.callback;
	}

	runCallback(type) {
		this.callback({
			type,
			index: this.index,
			length: this.stack.length,
			canUndo: this.canUndo(),
			canRedo: this.canRedo()
		});
		return this;
	}

	lock(lock = true) {
		this.isLocked = lock;
		return this;
	}

	unlock() {
		return this.lock(false);
	}

	setUndoRedo(func) {
		this.onUndoRedo = func;
		return this;
	}

	setCallback(func) {
		this.callback = func;
		return this;
	}

	init(data) {
		return this.add(data).unlock();
	}

	add(data) {
		if(this.timer) {
			clearTimeout(this.timer);
		}

		this.timer = setTimeout(() => {
			// if called after undoing, remove stack above this index
			this.stack.splice(this.index + 1, this.stack.length - this.index);

			// remove oldest item if we surpass maxSize
			if(this.stack.length === this.maxSize) {
				this.stack.shift();
			}

			this.stack.push(JSON.stringify(data));

			this.index = this.stack.length - 1;

			this.runCallback('add');
		}, this.undoGroupingInterval);

		return this;
	}

	undoOrRedo(type) {
		const
			isUndo = type === 'undo',
			index = this.index + (isUndo ? -1 : 1),
			data = this.stack[index];

		if(!data) {
			return this;
		}

		this.onUndoRedo(data);

		isUndo ? this.index-- : this.index++;

		this.runCallback(type);

		return this;
	}

	undo() {
		return this.undoOrRedo('undo');
	}

	redo() {
		return this.undoOrRedo('redo');
	}

	clear() {
		this.stack = [];
		this.index = -1;
		return this;
	}

	canUndo() {
		return !this.isLocked && this.index > 0;
	}

	canRedo() {
		return !this.isLocked && this.index < this.stack.length - 1;
	}
}
