<?php

/**
 * Returns request-style attributes for a given model instance; attempts
 * to take attribute casts into account.
 *
 * @param \Illuminate\Database\Eloquent\Model $model
 * @param array $merge
 * @return array
 */
function attrs_for(\Illuminate\Database\Eloquent\Model $model, $merge = []){
    $attrs = $model->getAttributes();

    // We need to add casts support...
    $casts = $model->getCasts();

    // ...and have to use reflection as 'castAttribute' is protected...
    $refl = new ReflectionObject($model);
    $method = $refl->getMethod('castAttribute');
    $method->setAccessible(true);

    foreach($attrs as $key => $value){
        $snakeKey = snake_case($key);

        $attrs[$snakeKey] = array_key_exists($key, $casts) ? $method->invoke($model, $key, $value) : $value;
        if($key !== $snakeKey) unset($attrs[$key]);
    }

    return array_merge($attrs, $merge);
}
