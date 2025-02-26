<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Musica",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="visualizacoes", type="integer", example=1),
 *     @OA\Property(property="titulo", type="string", example="Título da musica"),
 *     @OA\Property(property="youtube_id", type="string", example="youtube_id"),
 *     @OA\Property(property="thumb", type="string", example="thumb"),
 * )
 */
class Musica extends Model
{
    protected $table = 'musicas';
    protected $fillable = ['visualizacoes', 'titulo', 'youtube_id', 'thumb', 'url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
