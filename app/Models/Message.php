<?php

namespace App\Models;

use Crypt;
use App\Enums\MessageType;
use App\Enums\MessageStatus;
use Illuminate\Support\Collection;
use App\Traits\PerformsTransactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

/**
 * App\Models\Message
 *
 * @property string $id
 * @property int $chat_id
 * @property int $sender_id
 * @property string $type
 * @property string|null $text
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $sending_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $seen_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Chat $chat
 * @property-read \App\Models\DeletedMessage|null $deletedMessage
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MessagePicture[] $pictures
 * @property-read int|null $pictures_count
 * @property-read \App\Models\User $sender
 * @method static \Database\Factories\MessageFactory factory(...$parameters)
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message ofChat(\App\Models\Chat $chat)
 * @method static Builder|Message ofType(string $type)
 * @method static Builder|Message ofTypePicture()
 * @method static Builder|Message ofUser(\App\Models\User $user)
 * @method static Builder|Message query()
 * @method static Builder|Message seen()
 * @method static Builder|Message sending()
 * @method static Builder|Message unseen()
 * @method static Builder|Message whereChatId($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereDeliveredAt($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereSeenAt($value)
 * @method static Builder|Message whereSenderId($value)
 * @method static Builder|Message whereSendingAt($value)
 * @method static Builder|Message whereStatus($value)
 * @method static Builder|Message whereText($value)
 * @method static Builder|Message whereType($value)
 * @method static Builder|Message whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Message extends Model
{
	use PerformsTransactions,
		Uuid,
		HasFactory;


	/**
	 * The "type" of the auto-incrementing ID.
	 *
	 * @var string
	 */
	protected $keyType = 'string';

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * @var string[]
	 */
	protected $guarded = ['id'];

	/**
	 * @var string[]
	 */
	protected $touches = ['chat'];

	/**
	 * @var string[]
	 */
	protected $casts = [
		'sending_at' => 'datetime',
		'delivered_at' => 'datetime',
		'seen_at' => 'datetime'
	];



	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function (Message $message) {
			$message->type = $message->type ?? MessageType::TEXT;
			$message->status = $message->status ?? MessageStatus::SENDING;
			$message->sending_at = $message->sending_at ?? now();
		});
	}

	/**
	 * @param array $ids
	 * @return int
	 */
	public static function markDelivered(array $ids): int
	{
		return static::sending()
			->whereIn('id', $ids)
			->update(['status' => MessageStatus::DELIVERED, 'delivered_at' => now()]);
	}

	/**
	 * @param array $ids
	 * @return int
	 */
	public static function markSeen(array $ids): int
	{
		return static::unseen()
			->whereIn('id', $ids)
			->update(['status' => MessageStatus::SEEN, 'seen_at' => now()]);
	}



	/**
	 * @return BelongsTo
	 */
	public function chat(): BelongsTo
	{
		return $this->belongsTo(Chat::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function sender(): BelongsTo
	{
		return $this->belongsTo(User::class, 'sender_id');
	}

	/**
	 * @return HasMany
	 */
	public function pictures(): HasMany
	{
		return $this->hasMany(MessagePicture::class);
	}

	/**
	 * @return HasOne
	 */
	public function deletedMessage(): HasOne
	{
		return $this->hasOne(DeletedMessage::class);
	}



	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public function getTextAttribute(?string $value): ?string
	{
		return is_null($value) ? $value : Crypt::decryptString($value);
	}

	/**
	 * @param string|null $value
	 * @return void
	 */
	public function setTextAttribute(?string $value): void
	{
		$this->attributes['text'] = is_null($value) ? $value : Crypt::encryptString($value);
	}



	/**
	 * @param Builder|Message $query
	 * @param Chat $chat
	 * @return Builder|Message
	 */
	public function scopeOfChat(Builder $query, Chat $chat): Builder
	{
		return $query->where('chat_id', $chat->id);
	}

	/**
	 * @param Builder|Message $query
	 * @param User $user
	 * @return Builder|Message
	 */
	public function scopeOfUser(Builder $query, User $user): Builder
	{
		return $query->whereDoesntHave(
			'deletedMessage',
			fn(Builder $query) => $query->where('user_id', $user->id)
		);
	}

	/**
	 * @param Builder|Message $query
	 * @param string $type
	 * @return Builder|Message
	 */
	public function scopeOfType(Builder $query, string $type): Builder
	{
		return $query->where('type', $type);
	}

	/**
	 * @param Builder|Message $query
	 * @return Builder|Message
	 */
	public function scopeOfTypePicture(Builder $query): Builder
	{
		return $query->ofType(MessageType::PICTURE);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeSending(Builder $query): Builder
	{
		return $query->where('status', '=', MessageStatus::SENDING);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeSeen(Builder $query): Builder
	{
		return $query->where('status', '=', MessageStatus::SEEN);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeUnseen(Builder $query): Builder
	{
		return $query->where('status', '!=', MessageStatus::SEEN);
	}



	/**
	 * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @param bool $preserveOriginal
	 * @return MessagePicture
	 * @throws \Throwable
	 */
	public function addPicture($file, bool $preserveOriginal = false): MessagePicture
	{
		return self::performTransaction(
			fn() => $this->pictures()->create()->addFile($file, $preserveOriginal)
		);
	}

	/**
	 * @param array|Collection $files
	 * @param bool $preserveOriginal
	 * @return Collection|MessagePicture[]
	 * @throws \Throwable
	 */
	public function addPictures($files, bool $preserveOriginal = false): Collection
	{
		if (!$files instanceof Collection) {
			$files = collect($files);
		}

		return $files->map(fn($file) => $this->addPicture($file, $preserveOriginal));
	}

	/**
	 * @return bool]
	 */
	public function isText(): bool
	{
		return $this->type == MessageType::TEXT;
	}

	/**
	 * @return bool
	 */
	public function isPicture(): bool
	{
		return $this->type == MessageType::PICTURE;
	}

	/**
	 * @param Chat $chat
	 * @return bool
	 */
	public function belongsToChat(Chat $chat): bool
	{
		return $this->chat_id == $chat->id;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function sentByUser(User $user): bool
	{
		return $this->sender_id == $user->id;
	}
}
