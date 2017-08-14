<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Page;

/**
 * Globally restricts all requests for Pages to a specific version of a Site.
 * @package App\Models\Scopes
 */
class VersionScope implements Scope
{
    // The version we are applying
    public $version = Page::STATE_DRAFT;

    /**
     * Create the Scope with the version to apply.
     * @param string $version
     */
    public function __construct($version = null)
    {
        if(null != $version){
            $this->version = $version;
        }
    }

    /**
     * Apply the scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('version',  $this->version);
    }
}