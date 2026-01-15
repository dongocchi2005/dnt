<?php

namespace App\Services\Chat;

use App\Models\KnowledgeBase;
use Illuminate\Support\Str;

class KnowledgeBaseSearch
{
    public function search(string $query, int $limit = 3): array
    {
        $q = trim($query);
        if ($q === '') {
            return [];
        }

        $terms = collect(preg_split('/\s+/u', Str::lower($q)))
            ->map(fn($t) => trim((string)$t))
            ->filter(fn($t) => $t !== '' && mb_strlen($t) > 2)
            ->values()
            ->all();

        $builder = KnowledgeBase::query()
            ->where('is_active', true)
            ->where(function ($qb) use ($q, $terms) {
                $qb->where('title', 'like', '%' . $q . '%')
                    ->orWhere('content', 'like', '%' . $q . '%');
                foreach ($terms as $term) {
                    $qb->orWhere('title', 'like', '%' . $term . '%')
                        ->orWhere('content', 'like', '%' . $term . '%');
                }
            })
            ->orderBy('updated_at', 'desc')
            ->limit($limit);

        return $builder->get()->map(function (KnowledgeBase $kb) {
            return [
                'id' => $kb->id,
                'title' => (string)$kb->title,
                'category' => (string)$kb->category,
                'content' => (string)$kb->content,
                'source_type' => (string)$kb->source_type,
            ];
        })->all();
    }
}
