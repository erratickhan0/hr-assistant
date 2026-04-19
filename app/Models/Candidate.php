<?php

namespace App\Models;

use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property int $organization_id
 * @property string $uuid
 * @property string|null $display_name
 * @property string|null $email
 * @property string|null $phone
 * @property string $source
 * @property array<string, mixed>|null $metadata
 */
class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'organization_id',
        'display_name',
        'email',
        'phone',
        'source',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::creating(function (Candidate $candidate): void {
            if ($candidate->uuid === null || $candidate->uuid === '') {
                $candidate->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<CandidateDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CandidateDocument::class);
    }
}
