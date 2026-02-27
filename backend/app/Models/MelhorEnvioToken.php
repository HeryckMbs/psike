<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MelhorEnvioToken extends Model
{
    use HasFactory;

    protected $table = 'melhor_envio_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'expires_at',
        'token_type',
        'scope',
        'active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * Verifica se o token está válido (não expirado)
     */
    public function isValid(): bool
    {
        return $this->active && $this->expires_at && $this->expires_at->isFuture();
    }

    /**
     * Verifica se o token está expirado ou próximo de expirar (margem de 5 minutos)
     */
    public function isExpiredOrNearExpiry(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        // Considera expirado se faltar menos de 5 minutos
        // Usa copy() para não modificar o original
        return $this->expires_at->copy()->subMinutes(5)->isPast();
    }

    /**
     * Busca o token ativo mais recente
     */
    public static function getActiveToken()
    {
        return static::where('active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Desativa todos os tokens
     */
    public static function deactivateAll()
    {
        return static::where('active', true)->update(['active' => false]);
    }
}
