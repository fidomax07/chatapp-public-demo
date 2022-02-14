<?php

namespace App\Models;

use App\Traits\HashesIntegers;
use App\Traits\EncodesFileNames;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use App\Traits\PerformsTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string|null $hash_id
 * @property string $phone
 * @property string $pin
 * @property string|null $username
 * @property int|null $sms_vc
 * @property \Illuminate\Support\Carbon|null $sms_vc_generated_at
 * @property int $sms_vc_attempts
 * @property int $sms_vc_sents
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property bool $ntf_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\App\Models\Chat[] $chats
 * @property-read int|null $chats_count
 * @property-read Collection|User[] $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection|\App\Models\DeletedMessage[] $deletedMessages
 * @property-read int|null $deleted_messages_count
 * @property-read Collection|\App\Models\FcmToken[] $fcmTokens
 * @property-read int|null $fcm_tokens_count
 * @property-read string $full_name
 * @property-read MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read Collection|\App\Models\Message[] $messages
 * @property-read int|null $messages_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|\App\Models\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereHashId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereNtfEnabled($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePin($value)
 * @method static Builder|User whereSmsVc($value)
 * @method static Builder|User whereSmsVcAttempts($value)
 * @method static Builder|User whereSmsVcGeneratedAt($value)
 * @method static Builder|User whereSmsVcSents($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereVerifiedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia
{
	use HasFactory,
		HasApiTokens,
		HashesIntegers,
		PerformsTransactions,
		Notifiable,
		InteractsWithMedia,
		EncodesFileNames;

	public const MAX_SMSVC_TTL = 300;
	public const MAX_SMSVC_ATTEMPTS = 3;
	public const MAX_SMSVC_SENTS = 3;

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var string[]|bool
	 */
	protected $guarded = ['id'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'pin',
		'sms_vc',
		'sms_vc_generated_at',
		'sms_vc_attempts',
		'sms_vc_sents'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'sms_vc_generated_at' => 'datetime',
		'sms_vc_attempts' => 'integer',
		'sms_vc_sents' => 'integer',
		'verified_at' => 'datetime',
		'ntf_enabled' => 'boolean'
	];



	/**
	 * @param string|null $value
	 * @return int|null
	 */
	public function getSmsVcAttribute(?string $value): ?int
	{
		try {
			return (int)Crypt::decryptString($value);
		} catch (DecryptException $e) {
			return null;
		}
	}

	/**
	 * @param int|null $value
	 * @return void
	 */
	public function setSmsVcAttribute(?int $value): void
	{
		$this->attributes['sms_vc'] = $value ?
			Crypt::encryptString((string)$value) : null;
	}

	/**
	 * @return string
	 */
	public function getFullNameAttribute(): string
	{
		return "$this->first_name $this->last_name";
	}



	/**
	 * @return BelongsToMany
	 */
	public function contacts(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_contact', 'user_id', 'contact_id');
	}

	/**
	 * @return BelongsToMany
	 */
	public function chats(): BelongsToMany
	{
		return $this->belongsToMany(Chat::class)->latest();
	}

	/**
	 * @return HasMany
	 */
	public function messages(): HasMany
	{
		return $this->hasMany(Message::class, 'sender_id');
	}

	/**
	 * @return HasMany
	 */
	public function deletedMessages(): HasMany
	{
		return $this->hasMany(DeletedMessage::class);
	}

	/**
	 * @return HasMany
	 */
	public function fcmTokens(): HasMany
	{
		return $this->hasMany(FcmToken::class)->orderBy('created_at', 'desc');
	}



	/**
	 * Route notifications for the Nexmo channel.
	 *
	 * @return string
	 */
	public function routeNotificationForNexmo(): string
	{
		return $this->phone;
	}

	/**
	 * Specifies the user's FCM tokens
	 *
	 * @return array
	 */
	public function routeNotificationForFcm(): array
	{
		return $this->loadMissing('fcmTokens')
			->fcmTokens
			->pluck('value')
			->toArray();
	}



	/**
	 * @return void
	 */
	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('avatar')
			->useDisk('avatar')
			->singleFile()
			->useFallbackUrl(config('app.url') . '/avatar/default_avatar.png');
	}



	/**
	 * @param string $phone
	 * @return User|null
	 */
	public static function findByPhone(string $phone): ?User
	{
		return self::where('phone', self::sanitizePhone($phone))->first();
	}

	/**
	 * @param string $username
	 * @return User|null
	 */
	public static function findByUsername(string $username): ?User
	{
		return self::where('username', $username)->first();
	}

	/**
	 * @param array $credentials
	 * @return User|null
	 */
	public static function identify(array $credentials): ?User
	{
		$user = self::findByPhone($credentials['phone']);
		if (!$user || !$user->checkPin($credentials['pin'])) {
			return null;
		}
		return $user;
	}

	/**
	 * @param array $attributes
	 * @return User
	 * @throws \Throwable
	 */
	public static function store(array $attributes = []): User
	{
		return self::performTransaction(function () use ($attributes) {
			$user = self::create(collect($attributes)
				->map(function ($val, $key) {
					if ($key == 'phone') return self::sanitizePhone($val);
					if ($key == 'pin') return bcrypt($val);
					return $val;
				})
				->toArray());

			$user->generateHashId()
				->generateUsername()
				->generateSmsVc()
				->save();

			return $user;
		});
	}

	/**
	 * @param $rawPhone
	 * @return string
	 */
	protected static function sanitizePhone($rawPhone): string
	{
		return '+' . preg_replace('/\D+/', '', $rawPhone);
	}


	/**
	 * @param int $smsVc
	 * @return bool
	 */
	public function verifySmsVc(int $smsVc): bool
	{
		if ($smsVc != $this->sms_vc) {
			$this->incrementSmsVcAttempts()->save();
			return false;
		}

		return $this->clearSmsVc()
			->resetSmsVcAttempts()
			->resetSmsVcSents()
			->save();
	}

	/**
	 * @return bool
	 */
	public function verify(): bool
	{
		$this->verified_at = $this->freshTimestamp();
		return $this->save();
	}

	/**
	 * @return bool
	 */
	public function recordSmsVcSent(): bool
	{
		return $this->incrementSmsVcSent()->save();
	}

	/**
	 * @return bool
	 */
	public function regenerateSmsVc(): bool
	{
		return $this->generateSmsVc()
			->resetSmsVcAttempts()
			->save();
	}

	/**
	 * @return bool
	 */
	public function resetSmsVc(): bool
	{
		return $this->clearSmsVc()
			->resetSmsVcAttempts()
			->resetSmsVcSents()
			->save();
	}

	/**
	 * @param array $nameData
	 */
	public function updateName(array $nameData)
	{
		$this->update([
			'first_name' => $nameData['first_name'] ?? $this->first_name,
			'last_name' => $nameData['last_name'] ?? $this->last_name,
		]);
	}

	/**
	 * @param string $pin
	 * @return bool
	 */
	public function updatePin(string $pin): bool
	{
		$this->pin = bcrypt($pin);
		return $this->save();
	}

	/**
	 * @param string|UploadedFile $file
	 * @return Media
	 * @throws FileDoesNotExist
	 * @throws FileIsTooBig
	 */
	public function saveAvatar($file): Media
	{
		/** @var Media $media */
		$media = $this->addMedia($file)
			->sanitizingFileName(fn($fileName) => $this->getEncodedFileName($fileName))
			->toMediaCollection('avatar');

		return $media;
	}

	/**
	 * @param string $pin
	 * @return bool
	 */
	public function checkPin(string $pin): bool
	{
		return Hash::check($pin, $this->pin);
	}

	/**
	 * @return bool
	 */
	public function smsVcExpired(): bool
	{
		$secondsPassed = $this->freshTimestamp()
			->diffInSeconds($this->sms_vc_generated_at);

		return $secondsPassed > self::MAX_SMSVC_TTL;
	}

	/**
	 * @return int
	 */
	public function smsVcValidity(): int
	{
		return self::MAX_SMSVC_TTL / 60;
	}

	/**
	 * @return bool
	 */
	public function maxSmsVcAttemptsReached(): bool
	{
		return $this->sms_vc_attempts == self::MAX_SMSVC_ATTEMPTS;
	}

	/**
	 * @return bool
	 */
	public function maxSmsVcSentsReached(): bool
	{
		return $this->sms_vc_sents == self::MAX_SMSVC_SENTS;
	}

	/**
	 * @return bool
	 */
	public function verified(): bool
	{
		return !is_null($this->verified_at);
	}

	/**
	 * @param Chat $chat
	 * @return bool
	 */
	public function hasChat(Chat $chat): bool
	{
		return $this->chats()->where('id', $chat->id)->value('id') != null;
	}

	/**
	 * @param User $contact
	 * @return bool
	 *
	 */
	public function hasContact(User $contact): bool
	{
		return $this->contacts()->where('id', $contact->id)->value('id') != null;
	}

	/**
	 * @return Builder|User
	 */
	public function contactsWithMedia()
	{
		/** @var Builder|User $contacts */
		$contacts = $this->contacts()->with('media');
		return $contacts;
	}

	/**
	 * @return bool
	 */
	public function hasNotificationsEnabled(): bool
	{
		return $this->ntf_enabled;
	}

	/**
	 * @param mixed $instance
	 * @return void
	 */
	public function pushNotify($instance)
	{
		if ($this->hasNotificationsEnabled()) {
			$this->notify($instance);
		}
	}


	/**
	 * @return $this
	 */
	protected function generateHashId(): User
	{
		$this->hash_id = $this->hashInt($this->id, 20);
		return $this;
	}

	/**
	 * @return User
	 */
	protected function generateUsername(): User
	{
		$this->username = $this->hashInt($this->id);
		return $this;
	}

	/**
	 * @return User
	 */
	protected function generateSmsVc(): User
	{
		$this->sms_vc = mt_rand(10000, 99999);
		$this->sms_vc_generated_at = $this->freshTimestamp();
		return $this;
	}

	/**
	 * @return User
	 */
	protected function clearSmsVc(): User
	{
		$this->sms_vc = null;
		$this->sms_vc_generated_at = null;
		return $this;
	}

	/**
	 * @return User
	 */
	protected function incrementSmsVcAttempts(): User
	{
		++$this->sms_vc_attempts;
		if ($this->sms_vc_attempts == self::MAX_SMSVC_ATTEMPTS) {
			$this->sms_vc = null;
		}
		return $this;
	}

	/**
	 * @return User
	 */
	protected function resetSmsVcAttempts(): User
	{
		$this->sms_vc_attempts = 0;
		return $this;
	}

	/**
	 * @return $this
	 */
	protected function incrementSmsVcSent(): User
	{
		++$this->sms_vc_sents;
		return $this;
	}

	/**
	 * @return $this
	 */
	protected function resetSmsVcSents(): User
	{
		$this->sms_vc_sents = 0;
		return $this;
	}
}
