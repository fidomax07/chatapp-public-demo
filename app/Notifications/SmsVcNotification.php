<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\NexmoMessage;

class SmsVcNotification extends Notification
{
	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @return array
	 */
	public function via(): array
	{
		return ['nexmo'];
	}

	/**
	 * Get the Vonage / SMS representation of the notification.
	 *
	 * @param User $notifiable
	 * @return NexmoMessage
	 */
	public function toNexmo(User $notifiable): NexmoMessage
	{
		return (new NexmoMessage)
			->content('ChatApp code: ' . $notifiable->sms_vc .
				' Valid for ' . $notifiable->smsVcValidity() . ' minutes.'
			)
			->unicode();
	}
}
