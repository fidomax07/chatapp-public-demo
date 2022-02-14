<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContactAddedDbNotification extends Notification
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
		return ['database'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @return array
	 */
	public function toDatabase(/*User $notifiable*/): array
	{
		return collect($this->data)
			->mapWithKeys(function ($val, $key) {
				return ["user_$key" => $val];
				// return ($key == 'id') ? ["user_$key" => $val] : [$key => $val];
			})->toArray();
	}
}
