<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMutasiHd extends Model
{
    protected $table = 'TRMUTASIHD';
    protected $primaryKey = 'NO_TRANSAKSI';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
