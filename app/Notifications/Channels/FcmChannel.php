<?php


namespace App\Notifications\Channels;


use Kreait\Firebase\Messaging;
use Illuminate\Notifications\Notification;

class FcmChannel
{
	/**
	 * Send the given notification.
	 *
	 * @param mixed $notifiable
	 * @param Notification $notification
	 * @return Messaging\MulticastSendReport|void
	 * @throws \Kreait\Firebase\Exception\FirebaseException
	 * @throws \Kreait\Firebase\Exception\MessagingException
	 */
	public function send($notifiable, Notification $notification)
	{
		if (!$tokens = $notifiable->routeNotificationFor('fcm', $notification)) {
			return;
		}

		$message = $notification->toFcm($notifiable);

		$messaging = app(Messaging::class);
		$fcmValidTokens = $messaging->validateRegistrationTokens($tokens)['valid'];
		return $messaging->sendMulticast($message, $fcmValidTokens);
	}
}