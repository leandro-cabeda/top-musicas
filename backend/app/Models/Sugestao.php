<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Sugestao",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="titulo", type="string", example="Título da sugestão"),
 *     @OA\Property(property="youtube_id", type="string", example="youtube_id"),
 *     @OA\Property(property="status", type="string", example="pendente"),
 * )
 */
class Sugestao extends Model
{
    use HasFactory;

    protected $table = 'sugestoes';
    protected $fillable = ['user_id', 'titulo', 'youtube_id', 'status', 'url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
