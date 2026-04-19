<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'display_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'source' => 'public_upload',
            'metadata' => null,
        ];
    }
}
