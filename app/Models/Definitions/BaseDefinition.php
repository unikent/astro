<?php
namespace App\Models\Definitions;

use Config;
use Exception;
use JsonSerializable;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\JsonDecodeException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use App\Exceptions\MethodNotSupportedException;
use App\Exceptions\DefinitionNotFoundException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use App\Models\Definitions\Contracts\Definition as DefinitionContract;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

abstract class BaseDefinition implements Arrayable, DefinitionContract, Jsonable, JsonSerializable
{

    use HasAttributes, HidesAttributes, GuardsAttributes;

    protected static $defDir = '';

	/**
	 * Extract the name and version number from a definition identifier

	 * @param string $id - Definition identifier in the form {name}-v{version}

	 * @return array|null [ 'name' => {name}, 'version' => {version} ] or null if no match.
	 */
    public static function idToNameAndVersion($id)
	{
		if(preg_match('/^(.+)-v([0-9]+)$/', $id, $matches)){
			return [
				'name' => $matches[1],
				'version' => $matches[2]
			];
		}
		return null;
	}

	/**
	 * Get a version identifier string based on its name and version.

	 * @param string $name - The definition name.
	 * @param integer $version - The definition version.

	 * @return string {name}-v{version}
	 */
	public static function idFromNameAndVersion($name, $version)
	{
		return $name . '-v' . $version;
	}

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
		if(!empty($definition['dynamic'])){
        	// dynamic definitions have a definition class that can do things
        	$defn_id = static::idFromNameAndVersion($definition['name'], $definition['version']);
        	$class_path = static::definitionPath($defn_id);
			$class_name = static::getDynamicClassName($defn_id);
			$file_path = $class_path . '/' . $class_name. '.php';
			require_once $file_path;
			$instance = new $class_name();
		}
		else {
			$instance = new static();
		}
    	$instance->forceFill($definition);
		return $instance;
    }

	/**
	 * Get the names of all available dynamic attributes for this definition.
	 * @return array
	 */
    public function getDynamicAttributeNames()
	{
		$names = [];
		foreach($this->dynamicAttributes ?? [] as $attribute) {
			$names[] = $attribute['name'];
		}
		return $names;
	}

	/**
	 * Gets the name of the dynamic definition class.
	 * Dynamic definition classes are the definition name (first character uppercased),
	 * with any non-alpha-numeric characters removed (and the first following character uppercased)
	 * @param string $definition_id {name}-v{version}
	 * @return string - Class (and file, minus .php extension) name for the class that handles dynamic actions for this definition.
	 */
    public static function getDynamicClassName($definition_id)
	{
		$def = static::idToNameAndVersion($definition_id);
		$parts = preg_split('/[^a-z0-9]/i', $def['name'], -1, PREG_SPLIT_NO_EMPTY);
		$class_name = '';
		foreach($parts as $part) {
			$class_name .= ucfirst($part);
		}
		return $class_name . 'V' . $def['version'];
	}

    /**
     * Returns a new model instance based on a definition file.
     *
     * @param  string $path
     * @return DefinitionContract
     */
    public static function fromDefinitionFile($path)
    {
        $definition = null;

        if (Config::get('database.redis.active')) {
            try {
                $definition = Redis::get($path);
            } catch (Exception $e) {}
        }

        if (empty($definition)) {
            if(!file_exists($path)){
                throw new FileNotFoundException($path);
            }
            $definition = file_get_contents($path);
            if (Config::get('database.redis.active')) {
                try {
                    Redis::set($path, $definition);
                } catch (Exception $e) {}
            }
        }

    	return static::fromDefinition($definition);
    }

	/**
	 * Get the path to the folder containing the specified definition.
	 * @param string $definition_id - The {name}-v{version} string identifying the definition.
	 * @return null|string - The path or null if $definition_id is invalid.
	 */
    public static function definitionPath($definition_id)
	{
		$parts = static::idToNameAndVersion($definition_id);
		if($parts) {
			return sprintf('%s/%s/%s/v%d', Config::get('app.definitions_path'), static::$defDir, $parts['name'], $parts['version']);
		}
		return null;
	}

    /**
     * Locates a Definition file on disk; when no version is specified
     * it will return the latest.
     *
	 * @param  string $definition_id - The {name}-v{version} string identifying the definition.
     * @return string|null
     */
    public static function locateDefinition($definition_id){
    	$path = static::definitionPath($definition_id);
    	if($path) {
			$path .= '/definition.json';
			return file_exists($path) ? $path : null;
		}
		return null;
    }


    /**
     * Locates a Definition file on disk; throws an exception if no Definition is found.
     *
     * @param  string $definition_id - The {name}-v{version} string identifying the definition.
     * @param  int $version
     * @throws DefinitionNotFoundException
     * @return string
     */
    public static function locateDefinitionOrFail($definition_id){
        $path = static::locateDefinition($definition_id);
        if(is_null($path)) throw new DefinitionNotFoundException;

        return $path;
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
