<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected string $deepseekKey;
    protected string $deepseekUrl;
    protected string $deepseekModel;
    protected string $difyUrl;
    protected string $difyKey;

    public function __construct()
    {
        $this->deepseekKey = config('services.deepseek.key');
        $this->deepseekUrl = config('services.deepseek.url', 'https://api.deepseek.com/v1/chat/completions');
        $this->deepseekModel = config('services.deepseek.model', 'deepseek-chat');
        $this->difyUrl = config('services.dify.url', 'http://localhost/v1/workflows/run');
        $this->difyKey = config('services.dify.key');

        Log::info('ChatController: initialized', [
            'deepseek_configured' => !empty($this->deepseekKey),
            'deepseek_url' => $this->deepseekUrl,
            'deepseek_model' => $this->deepseekModel,
            'dify_configured' => !empty($this->difyKey),
            'dify_url' => $this->difyUrl,
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $user = $request->user();
        Log::info('Chat: request received', [
            'user_id' => $user?->id,
            'message_count' => count($request->input('messages', [])),
        ]);

        try {
            $validated = $request->validate([
                'messages' => 'required|array',
                'messages.*.role' => 'required|in:system,user,assistant',
                'messages.*.content' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Chat: validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $userMessage = end($validated['messages'])['content'] ?? '';
        $systemPrompt = $this->buildSystemPrompt($user, $userMessage);

        Log::info('Chat: user message', [
            'message_preview' => mb_substr($userMessage, 0, 100),
            'dify_configured' => !empty($this->difyKey),
            'deepseek_configured' => !empty($this->deepseekKey),
        ]);

        // Try Dify first if configured
        if ($this->difyKey) {
            Log::info('Chat: attempting Dify');
            try {
                $reply = $this->callDify($userMessage, $validated['messages']);
                Log::info('Chat: Dify succeeded', ['reply_length' => mb_strlen($reply)]);
                return response()->json([
                    'reply' => $reply,
                    'routed_to' => $this->detectRoute($userMessage)?->label,
                ]);
            } catch (\Exception $e) {
                Log::error('Chat: Dify API call failed, falling back to DeepSeek', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }

        // Fall back to DeepSeek
        if ($this->deepseekKey) {
            Log::info('Chat: attempting DeepSeek');
            try {
                $reply = $this->callDeepSeek($systemPrompt, $validated['messages']);
                Log::info('Chat: DeepSeek succeeded', ['reply_length' => mb_strlen($reply)]);
                return response()->json([
                    'reply' => $reply,
                    'routed_to' => $this->detectRoute($userMessage)?->label,
                ]);
            } catch (\Exception $e) {
                Log::error('Chat: DeepSeek API call failed', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'model' => $this->deepseekModel,
                    'url' => $this->deepseekUrl,
                ]);
            }
        }

        // Both failed or not configured — use local fallback
        Log::info('Chat: using local fallback');
        return response()->json([
            'reply' => $this->generateFallback($validated['messages']),
            'routed_to' => $this->detectRoute($userMessage)?->label,
        ]);
    }

    protected function callDeepSeek(string $systemPrompt, array $messages): string
    {
        Log::info('Chat: callDeepSeek start', [
            'system_prompt_length' => mb_strlen($systemPrompt),
            'message_count' => count($messages),
        ]);

        $apiMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        );

        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . $this->deepseekKey,
            'Content-Type' => 'application/json',
        ])->post($this->deepseekUrl, [
            'model' => $this->deepseekModel,
            'messages' => $apiMessages,
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        Log::info('Chat: callDeepSeek response', ['status' => $response->status()]);

        $response->throw();
        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? 'No response generated.';

        Log::info('Chat: callDeepSeek success', ['reply_length' => mb_strlen($content)]);

        return $content;
    }

    protected function callDify(string $userMessage, array $messages): string
    {
        Log::info('Chat: callDify start', [
            'message_length' => mb_strlen($userMessage),
            'history_count' => count($messages) - 1,
        ]);

        $previousMessages = array_slice($messages, 0, -1);

        $response = Http::timeout(60)->withHeaders(
            $this->difyKey ? ['Authorization' => 'Bearer ' . $this->difyKey] : []
        )->post($this->difyUrl, [
            'inputs' => [
                'prompt' => $userMessage,
                'messages' => $previousMessages,
            ],
            'response_mode' => 'blocking',
            'user' => 'zonrad',
        ]);

        Log::info('Chat: callDify response', ['status' => $response->status()]);

        $response->throw();
        $result = $response->json();
        $data = $result['data'] ?? $result;

        $reply = $data['outputs']['text']
            ?? $data['outputs']['reply']
            ?? $data['outputs']['output']
            ?? $data['answer']
            ?? 'No response generated.';

        $reply = preg_replace('/<think>[\s\S]*?<\/think>/', '', $reply);
        $reply = trim($reply);

        Log::info('Chat: callDify success', ['reply_length' => mb_strlen($reply)]);

        return $reply;
    }

    protected function buildSystemPrompt($user, string $message): string
    {
        $routing = $this->detectRoute($message);
        Log::info('Chat: building system prompt', [
            'routed_to' => $routing?->label,
            'user_id' => $user?->id,
        ]);

        $prompt = "You are Zonrad, an advanced AI workspace assistant. "
            . "You help users with questions, research, writing, analysis, and routing to specialized agents. "
            . "Be concise, accurate, and helpful. Use markdown for formatting when appropriate.";

        if ($routing) {
            $prompt .= "\n\nThe user's request relates to {$routing->label}. "
                . "If they need deep specialized work, suggest they open the {$routing->label} agent "
                . "which provides dedicated tools for this task.";
        }

        return $prompt;
    }

    protected function detectRoute(string $message): ?object
    {
        $lower = strtolower(trim($message));
        $rules = [
            (object)['label' => 'AI Docs', 'keywords' => ['document', 'write report', 'create proposal', 'draft', 'essay', 'write a', 'write an', 'proposal', 'report on', 'documentation', 'memo', 'letter', 'article', 'blog post', 'white paper', 'manual']],
            (object)['label' => 'AI Slides', 'keywords' => ['slides', 'presentation', 'pitch deck', 'slide deck', 'powerpoint', 'keynote', 'slideshow', 'deck', 'present']],
            (object)['label' => 'AI Fact Checker', 'keywords' => ['verify', 'fact check', 'is this true', 'check this', 'validate', 'fact-check', 'reliable', 'source', 'citation', 'claim', 'truth', 'misinformation', 'fake']],
            (object)['label' => 'AI Excel', 'keywords' => ['excel', 'spreadsheet', 'data analysis', 'table', 'csv', 'xlsx', 'worksheet']],
        ];

        foreach ($rules as $rule) {
            foreach ($rule->keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    Log::info('Chat: route detected', ['label' => $rule->label, 'keyword_matched' => $kw]);
                    return $rule;
                }
            }
        }

        Log::info('Chat: no route detected');
        return null;
    }

    protected function generateFallback(array $messages): string
    {
        Log::info('Chat: generateFallback called', ['message_count' => count($messages)]);

        $last = $messages[count($messages) - 1]['content'] ?? '';
        $lower = strtolower(trim($last));

        $routing = $this->detectRoute($last);

        // Build answer first, then append routing suggestion
        $answer = '';

        // Knowledge base
        $kb = [
            'capital of india' => "The capital of India is **New Delhi**.",
            'capital of france' => "The capital of France is **Paris**.",
            'capital of japan' => "The capital of Japan is **Tokyo**.",
            'capital of uk' => "The capital of the United Kingdom is **London**.",
            'capital of england' => "The capital of England is **London**.",
            'what is ai' => "**Artificial Intelligence (AI)** refers to the simulation of human intelligence by machines, especially computer systems. It includes subfields like machine learning, natural language processing, computer vision, and robotics.",
            'what is python' => "**Python** is a high-level, interpreted programming language known for its readability and versatility. It is widely used in web development, data science, AI/ML, automation, and more.",
        ];
        foreach ($kb as $key => $val) {
            if (str_contains($lower, $key)) { $answer = $val; break; }
        }

        if (!$answer) {
            if (str_contains($lower, 'hello') || str_contains($lower, 'hi ') || str_contains($lower, 'hey') || str_contains($lower, 'greetings')) {
                $answer = "Hello! I'm **Zonrad**, your AI workspace assistant. I can answer questions, help with research, draft documents, create presentations, and verify facts. Try asking me something!";
            } elseif (str_contains($lower, 'help') || str_contains($lower, 'what can you do') || str_contains($lower, 'capabilities')) {
                $answer = "Here's what I can do:\n\n💬 **General Q&A** — Answer questions and discuss topics\n📄 **AI Docs** — Draft reports, proposals, and documents\n📊 **AI Slides** — Structure presentations and slide decks\n🛡️ **AI Fact Checker** — Verify claims and validate sources";
            } elseif (str_contains($lower, 'thank') || str_contains($lower, 'thanks')) {
                $answer = "You're welcome! Happy to help. Let me know if there's anything else I can assist with.";
            } elseif (preg_match('/^(what|who|where|when|why|how|explain|define|tell me|describe)\b/', $lower)) {
                $answer = "That's a great question! I'd love to give you a detailed answer, but I currently need my AI backend connected to research topics like this. Here's what you can do:\n\n1. **Set up a DeepSeek API key** in your `.env` file (`DEEPSEEK_API_KEY=your_key`) for full AI-powered answers\n2. **Ask a more specific question** so I can try to help from my built-in knowledge\n3. **Use the Dify workflow** integration for web-search powered answers\n\nIn the meantime, try asking about something in my knowledge base like coding, AI, or world capitals!";
            } else {
                $answer = "I'm here to help! I can answer questions, assist with writing, explain concepts, and more. Here are some things you can ask me:\n\n• **General knowledge** — Ask me facts, explanations, or definitions\n• **Writing** — I can help draft documents, reports, and proposals\n• **Presentations** — I can structure slide decks and presentations\n• **Research** — I can help analyze and summarize information\n\nWhat would you like help with?";
            }
        }

        // Append routing suggestion if applicable
        if ($routing) {
            $routeTips = [
                'AI Docs' => "\n\n---\n📌 **Tip:** For advanced document creation with templates, open the **AI Docs Agent** from the dashboard.",
                'AI Slides' => "\n\n---\n📌 **Tip:** For polished slide decks with themes, open the **AI Slides Agent** from the dashboard.",
                'AI Fact Checker' => "\n\n---\n📌 **Tip:** For deep fact-checking with source validation, open the **AI Fact Checker** from the dashboard.",
                'AI Excel' => "\n\n---\n📌 **Tip:** For spreadsheets and data analysis, open the **AI Excel Agent** from the dashboard.",
            ];
            $answer .= $routeTips[$routing->label] ?? '';
        }

        return $answer;
    }
}
