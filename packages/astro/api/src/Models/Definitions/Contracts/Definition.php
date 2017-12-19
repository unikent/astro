<?php
namespace Astro\API\Models\Definitions\Contracts;

interface Definition {

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes);

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function forceFill(array $attributes);

    /**
     * Returns a JSON representation of the Definition
     * @return string
     */
    public function toDefinition();

    /**
     * Decodes a JSON-blob of definition-data and returns
     * a populated model instance.
     *
     * @param  string $json
     * @return DefinitionContract
     */
    public static function fromDefinition($json);

    /**
     * Returns a new model instance based on a definition file.
     *
     * @param  string $path
     * @return DefinitionContract
     */
    public static function fromDefinitionFile($path);

    /**
     * Locates a Definition file on disk; when no version is specified
     * it will return the latest.
     *
	 * @param  string $definition_id - The {name}-v{version} string identifying the definition.
     * @return string|null
     */
    public static function locateDefinition($definition_id);

    /**
     * Locates a Definition file on disk; throws an exception if no Definition is found.
     *
     * @param  string $name
     * @param  int $version
     * @throws Astro\API\Exceptions\DefinitionNotFoundException
     * @return string
     */
    public static function locateDefinitionOrFail($definition_id);

}
