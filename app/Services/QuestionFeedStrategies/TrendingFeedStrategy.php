<?php

declare(strict_types=1);

namespace App\Services\QuestionFeedStrategies;

use App\Contracts\QuestionFeedStrategyProvider;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class TrendingFeedStrategy implements QuestionFeedStrategyProvider
{
    public function getBuilder(): Builder
    {
        return Question::query()
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->limit(10)
            ->where('likes_count', '>', 0)
            ->where('created_at', '>=', now()->subHours(12));
    }
}
