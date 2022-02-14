<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use Kreait\Firebase\Messaging\CloudMessage as FcmMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class ContactAddedPushNotification extends Notification
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
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @return array
	 */
	public function via(/*User $notifiable*/): array
	{
		return [FcmChannel::class];
	}

	/**
	 * @return FcmMessage
	 */
	public function toFcm(/*User $notifiable*/): FcmMessage
	{
		return FcmMessage::new()
			->withNotification(FcmNotification::fromArray([
				'title' => "Someone added you as contact.",
				'body' => "{$this->data['full_name']} just added you as a contact!",
				'image' => 'https://icon-library.com/images/1-15-512_8467.png'
			]))
			->withData($this->data)
			->withDefaultSounds()
			->withHighestPossiblePriority();
	}
}
