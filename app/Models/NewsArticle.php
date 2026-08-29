<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'dek', 'body', 'image_path', 'category', 'league_id', 'team_id',
        'match_id', 'source', 'status', 'is_pinned', 'author', 'reviewed_by', 'reviewed_at',
        'rejection_reason', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // The review-queue approve() action stamps published_at itself, but an
    // editor can also set status to "Published" directly on the create/edit
    // form (e.g. publishing human-written news straight away) - that path
    // saves the model without ever calling approve(), so published_at was
    // staying null. Everything downstream (homepage ordering, Match
    // Spotlight, "newest first" queries) sorts and filters on published_at,
    // so a null value there silently pushes an otherwise-live article to
    // the bottom and out of every featured slot. Catch it here instead of
    // in every place that sets status, so it can't happen again regardless
    // of which path (form, approve(), tinker) sets status to published.

    private const CATEGORY_LABELS = [
        'match-report' => ['cat-report', 'Match Report'],
        'transfers' => ['cat-transfers', 'Transfers'],
        'analysis' => ['cat-analysis', 'Analysis'],
        'injury' => ['cat-report', 'Injury Update'],
        'club-news' => ['cat-opinion', 'Club News'],
    ];

    public function getCategoryBadgeClassAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category][0] ?? 'cat-opinion';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category][1] ?? 'Club News';
    }

    protected static function booted(): void
    {
        static::creating(function (NewsArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title).'-'.Str::random(6);
            }
        });

        static::saving(function (NewsArticle $article) {
            if ($article->status === 'published' && ! $article->published_at) {
                $article->published_at = now();
            }

            // Filament's upload editor already fixes every photo at
            // 1600x900, but a JPEG straight off that client-side canvas
            // export can still run 300-500KB - dimensions alone don't make
            // a "light" image, actual file weight does. Re-encode real
            // uploads (never the AI pipeline's .svg placeholders, never an
            // already-compressed .webp) as WebP at a quality that stays
            // visually sharp for a news photo while cutting real weight.
            // Only runs when the image actually changed, so editing other
            // fields on an existing article doesn't repeatedly re-compress
            // the same photo and slowly degrade it.
            if ($article->isDirty('image_path') && $article->image_path) {
                if ($lighter = self::compressUploadedImage($article->image_path)) {
                    $article->image_path = $lighter;
                }
            }
        });
    }

    /**
     * Re-encodes a real photo upload (jpg/png/gif) as WebP on the public
     * disk to cut its file size, leaving SVGs and already-webp files
     * untouched. Returns the new relative path on success, or null if
     * there was nothing to do (missing file, unsupported type, GD not
     * available) - callers should fall back to the original path.
     */
    private static function compressUploadedImage(string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path) || ! function_exists('imagewebp')) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            return null;
        }

        $fullPath = $disk->path($path);

        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
            'png' => @imagecreatefrompng($fullPath),
            'gif' => @imagecreatefromgif($fullPath),
        };

        if (! $image) {
            return null;
        }

        // Flatten any transparency onto white before encoding - a news
        // photo has no alpha channel to preserve, and leaving it
        // half-converted can render as a black background in some readers.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, false);

        $webpPath = Str::beforeLast($path, '.').'.webp';
        $webpFullPath = $disk->path($webpPath);

        $encoded = imagewebp($image, $webpFullPath, 78);
        imagedestroy($image);

        if (! $encoded || ! $disk->exists($webpPath)) {
            return null;
        }

        $disk->delete($path);

        return $webpPath;
    }

    /**
     * Resolves image_path to a real URL whether it's a static asset path
     * (e.g. seeded articles) or a path from Filament's storage-disk upload.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://', '/'])) {
            return $this->image_path;
        }

        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset($this->image_path);
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

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
