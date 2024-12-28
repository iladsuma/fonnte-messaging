<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Pastikan trait ini diimpor
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory; // Gunakan trait HasFactory

    protected $table = 'phones'; // Nama tabel (opsional, jika tidak sesuai dengan konvensi)

    protected $fillable = ['phone_number', 'group_name']; // Kolom yang bisa diisi
}
