<?php

namespace App\Jobs;

use App\Models\GeneratedPost;
use App\Models\RawContent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Facades\Ai;

class ProcessRawContentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly RawContent $rawContent,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Récupérer le blueprint associé
            $blueprint = $this->rawContent->campaignBlueprint;

            // Construire le prompt avec les règles du blueprint
            $prompt = $this->buildPrompt($blueprint, $this->rawContent->content);

            // Appel à Grok via le SDK laravel/ai avec Structured Output
            $response = Ai::provider('xai')
                ->model('grok-3')
                ->structured([
                    'type'       => 'object',
                    'properties' => [
                        'hook_propose' => [
                            'type'        => 'string',
                            'description' => 'A compelling hook for the post, maximum 280 characters',
                        ],
                        'body_points' => [
                            'type'  => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Key points of the content as an array of strings',
                        ],
                        'technical_readability_score' => [
                            'type'        => 'integer',
                            'description' => 'Technical readability score from 0 to 100',
                            'minimum'     => 0,
                            'maximum'     => 100,
                        ],
                        'suggested_hashtags' => [
                            'type'  => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Suggested hashtags as an array of strings',
                        ],
                        'tone_compliance_justification' => [
                            'type'        => 'string',
                            'description' => 'Justification of tone compliance with blueprint rules',
                        ],
                    ],
                    'required' => [
                        'hook_propose',
                        'body_points',
                        'technical_readability_score',
                        'suggested_hashtags',
                        'tone_compliance_justification',
                    ],
                ])
                ->generate($prompt);

            // Valider que la réponse contient bien les clés requises
            $data = $this->validateResponse($response);

            // Créer le GeneratedPost en base de données
            GeneratedPost::create([
                'raw_content_id'               => $this->rawContent->id,
                'hook_propose'                 => $data['hook_propose'],
                'body_points'                  => $data['body_points'],
                'technical_readability_score'  => $data['technical_readability_score'],
                'suggested_hashtags'           => $data['suggested_hashtags'],
                'tone_compliance_justification' => $data['tone_compliance_justification'],
                'status'                       => 'draft',
            ]);

            // Mettre à jour le statut du RawContent
            $this->rawContent->update(['status' => 'draft']);

            Log::info('ProcessRawContentJob: success', [
                'raw_content_id' => $this->rawContent->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessRawContentJob: failed', [
                'raw_content_id' => $this->rawContent->id,
                'error'          => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build the prompt with blueprint rules.
     */
    private function buildPrompt($blueprint, string $content): string
    {
        $rules = '';

        if ($blueprint) {
            $extraRules = is_array($blueprint->extra_rules)
                ? implode("\n- ", $blueprint->extra_rules)
                : '';

            $rules = "
BLUEPRINT RULES TO FOLLOW:
- Tone: {$blueprint->tone_description}
- Maximum characters: {$blueprint->max_characters}
- Maximum hashtags: {$blueprint->max_hashtags}
- Extra rules:
- {$extraRules}
            ";
        }

        return "
You are an expert content creator for X (Twitter).
Analyze the following raw content and transform it into an optimized post.

{$rules}

RAW CONTENT TO ANALYZE:
{$content}

Return a structured JSON response with:
- hook_propose: A compelling hook (max {$blueprint->max_characters} characters)
- body_points: Array of key points extracted from the content
- technical_readability_score: Score from 0 to 100
- suggested_hashtags: Array of relevant hashtags (max {$blueprint->max_hashtags})
- tone_compliance_justification: Explanation of how the tone matches the blueprint
        ";
    }

    /**
     * Validate the AI response contains all required keys.
     */
    private function validateResponse(mixed $response): array
    {
        $data = is_array($response) ? $response : (array) $response;

        $requiredKeys = [
            'hook_propose',
            'body_points',
            'technical_readability_score',
            'suggested_hashtags',
            'tone_compliance_justification',
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                throw new \Exception("Missing required key in AI response: {$key}");
            }
        }

        return $data;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessRawContentJob: permanently failed', [
            'raw_content_id' => $this->rawContent->id,
            'error'          => $exception->getMessage(),
        ]);

        $this->rawContent->update(['status' => 'archived']);
    }
}