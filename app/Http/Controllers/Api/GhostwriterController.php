<?php

namespace App\Http\Controllers\Api;

use App\Ai\Tools\GetCampaignRulesTool;
use App\Ai\Tools\GetPostHistoryTool;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChatMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\GeneratedPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class GhostwriterController extends Controller
{
    use AuthorizesRequests;

    /**
     * @group Ghostwriter Agent
     *
     * Send a chat message to the Ghostwriter AI agent for a specific post.
     *
     * @urlParam generatedPost int required The generated post ID. Example: 1
     *
     * @bodyParam message string required The chat message (1-2000 chars). Example: Can you make the hook more engaging?
     *
     * @response 200 {
     *     "message": "Message sent successfully.",
     *     "data": {
     *         "reply": "Sure! How about: 'Stop scrolling — this changes everything you know about AI.'",
     *         "conversation": {
     *             "id": 1,
     *             "generated_post_id": 1,
     *             "messages": [
     *                 {"role": "user", "content": "Can you make the hook more engaging?"},
     *                 {"role": "assistant", "content": "Sure! How about: 'Stop scrolling...'"}
     *             ],
     *             "created_at": "08/07/2026 14:30"
     *         }
     *     }
     * }
     * @responseField data.reply String The AI agent's response.
     * @responseField data.conversation Object The conversation with messages.
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     *
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "message": ["The message is required."]
     *     }
     * }
     */
    public function chat(StoreChatMessageRequest $request, GeneratedPost $generatedPost): JsonResponse
    {
        $this->authorize('view', $generatedPost);

        $user = $request->user();

        /** @var Conversation|null $conversation */
        $conversation = $user->conversations()
            ->where('generated_post_id', $generatedPost->id)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_id'             => $user->id,
                'generated_post_id'   => $generatedPost->id,
                'agent_conversation_uuid' => null,
            ]);
        }

        $post = $generatedPost->load(['rawContent.campaignBlueprint']);
        $blueprint = $post->rawContent->campaignBlueprint;

        $systemPrompt = $this->buildSystemPrompt($post, $blueprint);

        $agent = $this->createAgent($systemPrompt);

        if ($conversation->agent_conversation_uuid) {
            $agent->continue($conversation->agent_conversation_uuid, $user);
        } else {
            $agent->forUser($user);
        }

        // Save user message in our table
        $conversation->chatMessages()->create([
            'role'    => 'user',
            'content' => $request->message,
        ]);

        // Send to agent (SDK handles memory in its own tables)
        $response = $agent->prompt($request->message);

        // Store SDK conversation UUID after first exchange
        if (! $conversation->agent_conversation_uuid && $response->conversationId) {
            $conversation->update([
                'agent_conversation_uuid' => $response->conversationId,
            ]);
        }

        // Save assistant message in our table
        $conversation->chatMessages()->create([
            'role'    => 'assistant',
            'content' => $response->text,
        ]);

        $conversation->load('chatMessages');

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => [
                'reply' => $response->text,
                'conversation' => ConversationResource::make($conversation),
            ],
        ], 200);
    }

    /**
     * @group Ghostwriter Agent
     *
     * Get the chat history for a specific generated post.
     *
     * @urlParam generatedPost int required The generated post ID. Example: 1
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "generated_post_id": 1,
     *         "messages": [
     *             {"role": "user", "content": "Can you improve this post?"},
     *             {"role": "assistant", "content": "Sure! Here are some suggestions..."}
     *         ],
     *         "created_at": "08/07/2026 14:30"
     *     }
     * }
     *
     * @response 200 {
     *     "message": "No conversation found for this post.",
     *     "data": {
     *         "conversation": null,
     *         "messages": []
     *     }
     * }
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     */
    public function history(Request $request, GeneratedPost $generatedPost): JsonResponse
    {
        $this->authorize('view', $generatedPost);

        $user = $request->user();

        /** @var Conversation|null $conversation */
        $conversation = $user->conversations()
            ->where('generated_post_id', $generatedPost->id)
            ->with('chatMessages')
            ->first();

        if (! $conversation) {
            return response()->json([
                'message' => 'No conversation found for this post.',
                'data'    => [
                    'conversation' => null,
                    'messages'     => [],
                ],
            ], 200);
        }

        return response()->json([
            'data' => ConversationResource::make($conversation),
        ], 200);
    }

    private function buildSystemPrompt(GeneratedPost $post, $blueprint): string
    {
        return sprintf(
            "You are Ghostwriter, an expert content creation assistant for X (Twitter).\n\n"
            . "## Current Post Context\n"
            . "- Hook: %s\n"
            . "- Body Points: %s\n"
            . "- Readability Score: %d/100\n"
            . "- Suggested Hashtags: %s\n"
            . "- Tone Justification: %s\n"
            . "- Status: %s\n\n"
            . "## Campaign Blueprint\n"
            . "- Name: %s\n"
            . "- Tone Description: %s\n"
            . "- Max Characters: %d\n"
            . "- Max Hashtags: %d\n"
            . "- Extra Rules: %s\n\n"
            . "## Your Role\n"
            . "Help the user refine, improve, or discuss this post. You have access to tools:\n"
            . "1. **get_campaign_rules** – Fetch the up-to-date campaign blueprint rules by ID.\n"
            . "2. **get_post_history** – Fetch the full generated post details by ID.\n\n"
            . "Always use these tools when the user asks about rules or post details — do NOT hallucinate data. "
            . "Be concise, direct, and helpful. Keep responses under 500 characters when possible.",
            $post->hook_propose ?? 'N/A',
            ! empty($post->body_points) ? implode('; ', $post->body_points) : 'N/A',
            $post->technical_readability_score ?? 0,
            ! empty($post->suggested_hashtags) ? implode(', ', $post->suggested_hashtags) : 'None',
            $post->tone_compliance_justification ?? 'N/A',
            $post->status,
            $blueprint->name ?? 'N/A',
            $blueprint->tone_description ?? 'None',
            $blueprint->max_characters ?? 280,
            $blueprint->max_hashtags ?? 1,
            ! empty($blueprint->extra_rules) ? implode('; ', $blueprint->extra_rules) : 'None'
        );
    }

    private function createAgent(string $systemPrompt): Agent&Conversational&HasTools
    {
        return new class(
            instructions: $systemPrompt,
            tools: [
                new GetCampaignRulesTool,
                new GetPostHistoryTool,
            ],
        ) implements Agent, Conversational, HasTools {
            use Promptable;
            use RemembersConversations;

            public function __construct(
                private string $instructions,
                private array $tools,
            ) {}

            public function instructions(): string
            {
                return $this->instructions;
            }

            public function tools(): iterable
            {
                return $this->tools;
            }
        };
    }
}
