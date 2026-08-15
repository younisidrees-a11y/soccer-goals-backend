<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'dek', 'body', 'category', 'league_id', 'team_id',
        'match_id', 'source', 'status', 'author', 'reviewed_by', 'reviewed_at',
        'rejection_reason', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title).'-'.Str::random(6);
            }
        });
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchFixture::class, 'match_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by');
    }

    /** Send an AI draft (or a human draft) into the review queue. */
    public function submitForReview(): void
    {
        $this->update(['status' => 'pending_review']);
    }

    /** Editor approves: publish immediately. */
    public function approve(AdminUser $reviewer): void
    {
        $this->update([
            'status' => 'published',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'published_at' => now(),
        ]);
    }

    /** Editor rejects: archived with a reason, never goes live. */
    public function reject(AdminUser $reviewer, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
