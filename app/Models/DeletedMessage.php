<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

/**
 * App\Models\DeletedMessage
 *
 * @property string $id
 * @property string $message_id
 * @property int $user_id
 * @property-read \App\Models\Message $message
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeletedMessage whereUserId($value)
 * @mixin \Eloquent
 */
class DeletedMessage extends Model
{
	use Uuid;

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
	 * @var bool
	 */
	public $timestamps = false;



	/**
	 * @return BelongsTo
	 */
	public function message(): BelongsTo
	{
		return $this->belongsTo(Message::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}



	/**
	 * @param string $messageId
	 * @param int $userId
	 * @return array
	 * @throws \Exception
	 */
	public static function buildAttributes(string $messageId, int $userId): array
	{
		return [
			'id' => (new self())->generateUuid(),
			'message_id' => $messageId,
			'user_id' => $userId
		];
	}
}
