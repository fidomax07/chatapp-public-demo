<?php

namespace App\Models;

use App\Traits\EncodesFileNames;
use Spatie\MediaLibrary\HasMedia;
use App\Traits\PerformsTransactions;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

/**
 * App\Models\MessagePicture
 *
 * @property int $id
 * @property string $message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Message $message
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessagePicture whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MessagePicture extends Model implements HasMedia
{
	use PerformsTransactions, InteractsWithMedia, EncodesFileNames;


	/**
	 * @return BelongsTo
	 */
	public function message(): BelongsTo
	{
		return $this->belongsTo(Message::class);
	}



	/**
	 * @return void
	 */
	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('message_picture')
			->useDisk('message_picture')
			->singleFile()
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/bmp'])
			->useFallbackUrl(env('APP_URL') . '/message/picture/sample_msg_pic.jpg');
	}



	/**
	 * @param string|UploadedFile $file
	 * @param bool $preserveOriginal
	 * @return MessagePicture
	 * @throws FileDoesNotExist
	 * @throws FileIsTooBig
	 */
	public function addFile($file, bool $preserveOriginal = false): MessagePicture
	{
		$mediaFileAdder = $this->addMedia($file)
			->sanitizingFileName(fn($fileName) => $this->getEncodedFileName($fileName));
		if ($preserveOriginal) {
			$mediaFileAdder->preservingOriginal();
		}
		$mediaFileAdder->toMediaCollection('message_picture');

		return $this;
	}

	/**
	 * @return Media
	 */
	public function picture(): Media
	{
		/** @var Media $pictureMedia */
		$pictureMedia = $this->getFirstMedia('message_picture');
		return $pictureMedia;
	}
}
