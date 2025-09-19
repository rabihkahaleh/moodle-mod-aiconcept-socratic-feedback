<?php
/**
 * Socratic Code Coach (mod_aiconcept)
 * Academic Evaluation Only — Non-Commercial, No Redistribution
 * This research prototype accompanies the manuscript:
 * “Design and Prototype Evaluation of an AI-Augmented Programming Education Tool.”
 *
 * @package   mod_aiconcept
 * @license   Academic Evaluation License v1.0 (see LICENSE_EVALUATION.txt)
 * @copyright 2025 Rabih Kahaleh
 */

namespace mod_aiconcept\local\openai;
defined('MOODLE_INTERNAL') || die();

/**
 * OpenAI client for mod_aiconcept.
 * - Uses Chat Completions for gpt-3.5* models.
 * - Falls back to Responses API for newer models (4.1/4o/etc).
 * - Returns ['text'=>string, 'usage'=>array|null, 'raw'=>array].
 */
final class client {
    private string $apikey;
    private string $model;
    private bool   $stream;

    public function __construct(?string $apikey = null, ?string $model = null, ?bool $stream = null) {
        $this->apikey = (string)($apikey ?? get_config('mod_aiconcept', 'openai_api_key') ?? '');
        $this->model  = (string)($model  ?? get_config('mod_aiconcept', 'openai_model') ?? 'gpt-3.5-turbo-0125');
        $this->stream = (bool)($stream ?? (get_config('mod_aiconcept', 'enable_streaming') ?? 0));
    }

    private function system_prompt(): string {
        $p = (string)(get_config('mod_aiconcept', 'system_prompt') ?? '');
        return trim($p) !== '' ? $p
            : 'You are a pedagogy-aware Python tutor. Diagnose misconceptions, ask ONE Socratic question, and give ONE tiny, testable nudge.';
    }

    /**
     * Build Chat Completions messages from history + current prompt.
     * $history items are ['role'=>'student|assistant','content'=>string].
     */
    private function build_chat_messages(string $prompt, array $history): array {
        $msgs = [
            ['role' => 'system', 'content' => $this->system_prompt()],
        ];
        foreach ($history as $turn) {
            $role = strtolower($turn['role'] ?? 'student');
            $content = (string)($turn['content'] ?? '');
            $msgs[] = [
                'role'    => ($role === 'assistant') ? 'assistant' : 'user',
                'content' => $content,
            ];
        }
        $msgs[] = ['role' => 'user', 'content' => $prompt];
        return $msgs;
    }

    /** Build a single input_text block for the Responses API (used for 4.x models). */
    private function build_responses_text(string $prompt, array $history): string {
        $lines = [];
        $lines[] = "[System]\n" . $this->system_prompt();
        foreach ($history as $turn) {
            $role = strtoupper($turn['role'] ?? 'STUDENT');
            $content = (string)($turn['content'] ?? '');
            $lines[] = "[$role]\n{$content}";
        }
        $lines[] = "[STUDENT]\n" . $prompt;
        return implode("\n\n", $lines);
    }

    /** Core caller. Normalizes output across APIs. */
    public function respond(string $prompt, array $history = []): array {
        if ($this->apikey === '') {
            return ['text' => "⚠️ OpenAI API key is not configured.", 'usage' => null, 'raw' => []];
        }

        $is35 = (str_starts_with($this->model, 'gpt-3.5'));
        if ($is35) {
            return $this->call_chat_completions($prompt, $history);
        } else {
            return $this->call_responses_api($prompt, $history);
        }
    }

    /** Call /v1/chat/completions for 3.5 models. */
    private function call_chat_completions(string $prompt, array $history): array {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $payload = [
            'model'       => $this->model, // e.g., gpt-3.5-turbo-0125
            'messages'    => $this->build_chat_messages($prompt, $history),
            'temperature' => 0.2,
        ];
        [$http, $data, $err] = $this->post_json($endpoint, $payload);
        if ($err) {
            return ['text' => "⚠️ Network/OpenAI error: $err", 'usage' => null, 'raw' => ['http' => $http]];
        }
        if ($http >= 400) {
            $msg = $data['error']['message'] ?? "HTTP $http";
            return ['text' => "⚠️ OpenAI API error: $msg", 'usage' => null, 'raw' => $data];
        }
        $text  = (string)($data['choices'][0]['message']['content'] ?? '');
        $usage = $data['usage'] ?? null;
        if ($text === '') {
            $text = "⚠️ OpenAI returned no content.";
        }
        return ['text' => $text, 'usage' => $usage, 'raw' => $data];
    }

    /** Call /v1/responses for newer 4.x models (kept for future flexibility). */
    private function call_responses_api(string $prompt, array $history): array {
        $endpoint = 'https://api.openai.com/v1/responses';
        $payload = [
            'model'             => $this->model, // e.g., gpt-4.1 / gpt-4o
            'input_text'        => $this->build_responses_text($prompt, $history),
            'temperature'       => 0.2,
            'max_output_tokens' => 512,
        ];
        [$http, $data, $err] = $this->post_json($endpoint, $payload);
        if ($err) {
            return ['text' => "⚠️ Network/OpenAI error: $err", 'usage' => null, 'raw' => ['http' => $http]];
        }
        if ($http >= 400) {
            $msg = $data['error']['message'] ?? "HTTP $http";
            return ['text' => "⚠️ OpenAI API error: $msg", 'usage' => null, 'raw' => $data];
        }
        $text = '';
        if (!empty($data['output_text']) && is_string($data['output_text'])) {
            $text = $data['output_text'];
        } elseif (!empty($data['output']) && is_array($data['output'])) {
            $buf = [];
            foreach ($data['output'] as $item) {
                if (is_string($item)) $buf[] = $item;
                elseif (is_array($item) && isset($item['content']) && is_string($item['content'])) $buf[] = $item['content'];
            }
            $text = trim(implode("\n", $buf));
        }
        if ($text === '') {
            $text = "⚠️ OpenAI returned no text.";
        }
        $usage = $data['usage'] ?? null;
        return ['text' => $text, 'usage' => $usage, 'raw' => $data];
    }

    /** Helper: POST JSON with cURL. */
    private function post_json(string $url, array $payload): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apikey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 30,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($data)) $data = [];
        return [$http, $data, $err];
    }

    /**
     * Summarize transcript into a student-facing KB (used on instructor approval).
     */
    public function summarize_kb(array $turns): array {
        $system = "You are a concise educational summarizer. Given a student–tutor transcript about Python, produce a short, student-facing Knowledge Base in markdown:\n- Issues encountered (ordered), each with a one-line concept label.\n- Tiny illustrative snippet (≤5 lines) if useful.\n- The general rules the student should remember.\n- Transfer tips: where else this applies.\nKeep it under ~300 words.";

        if (stripos($this->model, 'gpt-3.5') === 0) {
            // Chat Completions path.
            $messages = [['role' => 'system', 'content' => $system]];
            foreach ($turns as $t) {
                $role = (strtolower($t['role'] ?? '') === 'assistant') ? 'assistant' : 'user';
                $messages[] = ['role' => $role, 'content' => (string)($t['content'] ?? '')];
            }
            $endpoint = 'https://api.openai.com/v1/chat/completions';
            $payload  = ['model' => $this->model, 'messages' => $messages, 'temperature' => 0.2];
            [$http, $data, $err] = $this->post_json($endpoint, $payload);
            if ($err) {
                return ['text' => "⚠️ Network/OpenAI error: $err", 'usage' => null, 'raw' => ['http' => $http]];
            }
            if ($http >= 400) {
                $msg = $data['error']['message'] ?? "HTTP $http";
                return ['text' => "⚠️ OpenAI API error: $msg", 'usage' => null, 'raw' => $data];
            }
            $text  = (string)($data['choices'][0]['message']['content'] ?? '');
            $usage = $data['usage'] ?? null;
            if ($text === '') { $text = "⚠️ OpenAI returned no content."; }
            return ['text' => $text, 'usage' => $usage, 'raw' => $data];

        } else {
            // Responses API path.
            $input = $system . "\n\nTRANSCRIPT:\n";
            foreach ($turns as $t) {
                $input .= strtoupper($t['role'] ?? 'STUDENT') . ': ' . (string)($t['content'] ?? '') . "\n\n";
            }
            $endpoint = 'https://api.openai.com/v1/responses';
            $payload  = ['model' => $this->model, 'input_text' => $input, 'temperature' => 0.2, 'max_output_tokens' => 512];
            [$http, $data, $err] = $this->post_json($endpoint, $payload);
            if ($err) {
                return ['text' => "⚠️ Network/OpenAI error: $err", 'usage' => null, 'raw' => ['http' => $http]];
            }
            if ($http >= 400) {
                $msg = $data['error']['message'] ?? "HTTP $http";
                return ['text' => "⚠️ OpenAI API error: $msg", 'usage' => null, 'raw' => $data];
            }
            $text = (string)($data['output_text'] ?? '');
            if ($text === '') { $text = "⚠️ OpenAI returned no text."; }
            $usage = $data['usage'] ?? null;
            return ['text' => $text, 'usage' => $usage, 'raw' => $data];
        }
    }
}
