<?php

namespace App\Notifications;

use App\Models\User;
use App\Enums\MessageType;
use Illuminate\Bus\Queueable;
use App\Http\Resources\MessageResource;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use Kreait\Firebase\Messaging\CloudMessage as FcmMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class NewMessagePushNotification extends Notification
{
	use Queueable;

	/**
	 * @var array
	 */
	private array $data;



	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct(MessageResource $msgResource)
	{
		$this->data = $msgResource->resolve();
		if ($this->data['type'] === MessageType::PICTURE) {
			$this->normalizePictureData();
		}
	}


	public function via(): array
	{
		return [FcmChannel::class];
	}

	/**
	 * @param User $notifiable
	 * @return FcmMessage
	 */
	public function toFcm(User $notifiable): FcmMessage
	{
		return FcmMessage::new()
			->withNotification(FcmNotification::fromArray([
				'title' => $this->data['sender_name'],
				'body' => $this->data['type'] === MessageType::TEXT ? $this->data['text'] : 'Picture',
				'image' => 'https://images.vexels.com/media/users/3/129759/isolated/preview/e57821f1317893d1c2d8e184d4f9d595-chat-bubble-icon-by-vexels.png'
			]))
			->withData($this->data)
			->withDefaultSounds()
			->withHighestPossiblePriority();
	}

	/**
	 * @return void
	 */
	protected function normalizePictureData()
	{
		$pictures = collect($this->data['pictures']->resolve())
			->mapWithKeys(function ($attributes, $index) {
				return collect($attributes)->mapWithKeys(
					fn($val, $key) => ["picture_{$index}_$key" => $val]
				);
			});

		$this->data = collect($this->data)
			->filter(fn($val, $key) => $key !== 'pictures')
			->merge($pictures)
			->all();
	}
}
