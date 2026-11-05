<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Model sem $connection: usa a conexão padrão do gerenciador.
 */
class Item extends Model
{
    protected $table = 'items';
    protected $guarded = [];
    public $timestamps = false;
}
