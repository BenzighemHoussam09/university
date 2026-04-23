<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleCatalog extends Model
{
    use HasFactory;

    protected $table = 'module_catalog';

    protected $fillable = ['name'];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'created_from_catalog_id');
    }
}
