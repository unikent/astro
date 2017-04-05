<?php
namespace App\Models\Definitions;

use Exception;
use JsonSerializable;
use App\Exceptions\JsonDecodeException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use App\Exceptions\MethodNotSupportedException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use App\Models\Definitions\Contracts\Definition as DefinitionContract;


abstract class BaseDefinition implements Arrayable, DefinitionContract, Jsonable, JsonSerializable
{

	use HasAttributes, HidesAttributes, GuardsAttributes;


    /**
     * Dynamically retrieve attributes on the model.
     *
     * Identical to implementation on Illuminate\Database\Eloquent\Model
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * Identical to implementation on Illuminate\Database\Eloquent\Model
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }


    /**
     * Get the attributes that should be converted to dates.
     *
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes,
     * which assumes our model is DB-backed / Eloquent.
     *
     * @return array
     */
    public function getDates()
    {
        return $this->dates;
    }


    /**
     * Get the casts array.
     *
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes,
     * which assumes our model is DB-backed / Eloquent.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }


    /**
     * Get an attribute from the model.
     *
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes,
     * which assumes our model is DB-backed / Eloquent.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }
    }


    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @throws Exception
     */
    public function relationsToArray()
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @throws MethodNotSupportedException
     */
    protected function getArrayableRelations()
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  string  $key
     * @throws MethodNotSupportedException
     */
    public function getRelationValue($key)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  string  $method
     * @throws MethodNotSupportedException
     */
    protected function getRelationshipFromMethod($method)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @throws MethodNotSupportedException
     */
    public function getOriginal($key = null, $default = null)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @throws MethodNotSupportedException
     */
    public function syncOriginal()
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  string  $attribute
     * @throws MethodNotSupportedException
     */
    public function syncOriginalAttribute($attribute)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  array|string|null  $attributes
     * @throws MethodNotSupportedException
     */
    public function isDirty($attributes = null)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  array|string|null  $attributes
     * @throws MethodNotSupportedException
     */
    public function isClean($attributes = null)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @throws MethodNotSupportedException
     */
    public function getDirty()
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }

    /**
     * This overrides the implementation in Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  string  $key
     * @throws MethodNotSupportedException
     */
    protected function originalIsNumericallyEquivalent($key)
    {
    	throw new MethodNotSupportedException('This method not supported on a Definition object.');
    }


    /**
     * Fill the model with an array of attributes.
     *
     * This imitates the implementation on Illuminate\Database\Eloquent\Model, and is
     * required by the Illuminate\Database\Eloquent\Concerns\HasAttributes trait.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            }
        }
        return $this;
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     *
     * This imitates the implementation on Illuminate\Database\Eloquent\Model, and is
     * required by Illuminate\Database\Eloquent\Concerns\HasAttributes.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function forceFill(array $attributes)
    {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }


    /**
     * Returns a JSON representation of the Definition
     *
     * @return string
     */
	public function toDefinition()
	{
		$this->toJson();
	}


	/**
	 * Decodes a JSON-blob of definition-data and returns
	 * a populated model instance.
	 *
	 * @param  string $json
	 * @return DefinitionContract
	 */
	public static function fromDefinition($json)
	{
		$definition = json_decode($json, TRUE);

        if(JSON_ERROR_NONE !== json_last_error()){
            throw new JsonDecodeException(json_last_error_msg());
        }

		$instance = new static();
		$instance->forceFill($definition);

		return $instance;
	}


	/**
	 * Returns a new model instance based on a definition file.
	 *
	 * @param  string $path
	 * @return DefinitionContract
	 */
	public static function fromDefinitionFile($path)
	{
		if(!file_exists($path)){
			throw new FileNotFoundException;
		}

		return static::fromDefinition(file_get_contents($path));
	}


    /**
     * Convert the model instance to an array.
     *
     * This is the same implementation as Illuminate\Database\Eloquent\Model.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }


    /**
     * Convert the model instance to JSON.
     *
     * This is the same implementation as Illuminate\Database\Eloquent\Model.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }
        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * This is the same implementation as Illuminate\Database\Eloquent\Model.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

}
