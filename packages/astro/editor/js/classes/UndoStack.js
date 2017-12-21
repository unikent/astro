/* global clearTimeout, setTimeout */

export default class UndoStack {

	defaultOptions = {
		maxSize: 30,
		lock: false,
		undoGroupingInterval: 400,
		onUndoRedo: () => {},
		callback: () => {}
	};

	/**
	 * Create a stack of changes and set up options.
	 *
	 * @param {Object}   opts - The options to merge with the defaults.
	 * @param {number}   opts.maxSize
	 * @param {boolean}  opts.lock
	 * @param {number}   opts.undoGroupingInterval
	 * @param {function} opts.onUndoRedo
	 * @param {function} opts.callback
	 */
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

	/**
	 * Runs the callback supplied in the inital options.
	 *
	 * @param      {string}  type    The type of action performed (for
	 * better debugging/feedback), currently add|undo|redo.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
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

	/**
	 * Set lock flag for undo/redo.
	 *
	 * @param      {boolean}  lock    Whether to lock or unlock.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
	lock(lock = true) {
		this.isLocked = lock;
		return this;
	}

	/**
	 * Alias for unlocking undo/redo
	 */
	unlock() {
		return this.lock(false);
	}

	/**
	 * Set function to run on undo/redo.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
	setUndoRedo(func) {
		this.onUndoRedo = func;
		return this;
	}

	/**
	 * Set the callback to run on every action.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
	setCallback(func) {
		this.callback = func;
		return this;
	}

	/**
	 * Shortcut to add initial data and unlock undo/redo.
	 */
	init(data) {
		return this.add(data).unlock();
	}

	/**
	 * Add data to the stack, and group changes based on grouping interval.
	 * Then run the callback with the "add" action type.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
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

			// run our callback and pass "add" as action type
			this.runCallback('add');

		}, this.undoGroupingInterval);

		return this;
	}

	/**
	 * Travel backwards or forwards through stack.
	 * Calls our undo/redo function and the generic callback.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
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

	/**
	 * Undo latest change.
	 */
	undo() {
		return this.undoOrRedo('undo');
	}

	/**
	 * Redo previous change.
	 */
	redo() {
		return this.undoOrRedo('redo');
	}

	/**
	 * Clear the whole undo stack and reset current index.
	 *
	 * @return     {Object}  "this" for method chaining.
	 */
	clear() {
		this.stack = [];
		this.index = -1;
		return this;
	}

	/**
	 * See if there is anything to undo and the stack isn't locked.
	 *
	 * @return     {boolean}  If we can undo changes.
	 */
	canUndo() {
		return !this.isLocked && this.index > 0;
	}

	/**
	 * See if there is anything to redo and the stack isn't locked.
	 *
	 * @return     {boolean}  If we can redo changes.
	 */
	canRedo() {
		return !this.isLocked && this.index < this.stack.length - 1;
	}
}
