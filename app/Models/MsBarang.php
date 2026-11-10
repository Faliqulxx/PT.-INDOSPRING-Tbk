<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsBarang extends Model
{
    protected $table = 'MSBARANG';
    protected $primaryKey = 'KODE_BARANG';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
