<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuotaExceededException extends Exception
{
    public function __construct(
        public string $metricKey,
        public int $current,
        public ?int $limit,
        string $message = 'Plan quota exceeded.',
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        $payload = [
            'message' => $this->getMessage(),
            'metric' => $this->metricKey,
            'current' => $this->current,
            'limit' => $this->limit,
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($payload, 422);
        }

        return redirect()->back()->withErrors(['quota' => $this->getMessage()]);
    }
}
