<?php

namespace App\Models;

use App\Traits\PerformsTransactions;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder as QBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * App\Models\Chat
 *
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\App\Models\DeletedMessage[] $deletedMessages
 * @property-read int|null $deleted_messages_count
 * @property-read Collection|\App\Models\Message[] $messages
 * @property-read int|null $messages_count
 * @property-read Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\ChatFactory factory(...$parameters)
 * @method static Builder|Chat newModelQuery()
 * @method static Builder|Chat newQuery()
 * @method static Builder|Chat ofUser(\App\Models\User $user)
 * @method static Builder|Chat ofUsers(int $firstUserId, int $secondUserId)
 * @method static Builder|Chat query()
 * @method static Builder|Chat whereCreatedAt($value)
 * @method static Builder|Chat whereId($value)
 * @method static Builder|Chat whereName($value)
 * @method static Builder|Chat whereUpdatedAt($value)
 * @method static Builder|Chat withDependents()
 * @method static Builder|Chat withUserMessages(\App\Models\User $user)
 * @method static Builder|Chat withUserMessagesCount(\App\Models\User $user)
 * @mixin \Eloquent
 */
class Chat extends Model
{
	use HasFactory, PerformsTransactions;


	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function (Chat $chat) {
			$chat->name = $chat->name ?? 'private';
		});
	}



	/**
	 * @return BelongsToMany
	 */
	public function users(): BelongsToMany
	{
		return $this->belongsToMany(User::class);
	}

	/**
	 * @return HasMany
	 */
	public function messages(): HasMany
	{
		return $this->hasMany(Message::class)->latest();
	}

	/**
	 * @return HasManyThrough
	 */
	public function deletedMessages(): HasManyThrough
	{
		return $this->hasManyThrough(DeletedMessage::class, Message::class);
	}



	/**
	 * @param Builder $query
	 * @param User $user
	 * @return Builder|Chat
	 */
	public function scopeOfUser(Builder $query, User $user): Builder
	{
		return $query->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
			->where('chat_user.user_id', $user->id);
	}

	/**
	 * @param Builder $query
	 * @param int $firstUserId
	 * @param int $secondUserId
	 * @return Builder|Chat
	 */
	public function scopeOfUsers(Builder $query, int $firstUserId, int $secondUserId): Builder
	{
		return $query
			->whereIn('id',
				fn(QBuilder $query) => $query->select('id')
					->from('chats')
					->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
					->where('chat_user.user_id', $firstUserId)
			)->whereIn('id',
				fn(QBuilder $query) => $query->select('id')
					->from('chats')
					->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
					->where('chat_user.user_id', $secondUserId)
			);
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeWithDependents(Builder $query): Builder
	{
		return $query->with($this->getDependents());
	}

	/**
	 * @param Builder $query
	 * @param User $user
	 * @return Builder|Chat
	 */
	public function scopeWithUserMessagesCount(Builder $query, User $user): Builder
	{
		return $query->withCount($this->getUserMessagesRelation($user));
	}

	/**
	 * @param Builder $query
	 * @param User $user
	 * @return Builder|Chat
	 */
	public function scopeWithUserMessages(Builder $query, User $user): Builder
	{
		/** @var Builder|Message $query */
		return $query->with([
			'messages' => fn($query) => $query->ofUser($user)
				->with('pictures.media')
				->limit(ApiController::LIMIT_PER_PAGE)
		]);
	}



	/**
	 * @param int $userId
	 * @param int $recipientId
	 * @return Chat|null
	 */
	public static function betweenUsers(int $userId, int $recipientId): ?Chat
	{
		return self::ofUsers($userId, $recipientId)->first();
	}

	/**
	 * @param User|Authenticatable $user
	 * @return Chat|Builder
	 */
	public static function userIndex(User $user)
	{
		return self::ofUser($user)
			->withDependents()
			->withUserMessagesCount($user)
			->withUserMessages($user)
			->latest();
	}



	/**
	 * @param User $user
	 * @return User
	 */
	public function interlocutor(User $user): User
	{
		return $this->users()->where('id', '!=', $user->id)->first();
	}

	/**
	 * @param User $user
	 * @return int
	 */
	public function userMessagesCount(User $user): ?int
	{
		return $this->loadUserMessagesCount($user)->messages_count;
	}

	/**
	 * @param User|Authenticatable $user
	 * @return Builder|Message
	 */
	public function userMessages(User $user)
	{
		return Message::ofChat($this)->ofUser($user)->latest();
	}

	/**
	 * @return Message[]|Builder[]|Collection
	 */
	public function pictureMessages()
	{
		return Message::ofChat($this)->ofTypePicture()->get();
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function deleteUserMessages(User $user): bool
	{
		$messagesToDelete = Message::ofChat($this)->ofUser($user)
			->pluck('id')
			->map(fn($mId) => DeletedMessage::buildAttributes($mId, $user->id))
			->toArray();

		return DeletedMessage::insert($messagesToDelete);
	}

	/**
	 * Eager-load Chat dependencies.
	 */
	public function loadDependents(): Chat
	{
		$this->load($this->getDependents());
		return $this;
	}

	/**
	 * @param User $user
	 * @return Chat
	 */
	public function loadUserMessagesCount(User $user): Chat
	{
		$this->loadCount($this->getUserMessagesRelation($user));
		return $this;
	}



	/**
	 * @return string[]
	 */
	private function getDependents(): array
	{
		return [
			'users:id,username,first_name,last_name',
			'users.media'
		];
	}

	/**
	 * @param User $user
	 * @return \Closure[]
	 */
	private function getUserMessagesRelation(User $user): array
	{
		/** @var Builder|Message $query */
		return [
			'messages' => fn($query) => $query->ofUser($user)
		];
	}
}
