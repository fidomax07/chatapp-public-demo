<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\FcmToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $value
 * @property string|null $device_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereValue($value)
 * @mixin \Eloquent
 */
class FcmToken extends Model
{
	/**
	 * @var string[]|bool
	 */
	protected $guarded = ['id'];



	/**
	 * @return BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
