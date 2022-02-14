<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Http\Resources\MessageResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * @var MessageResource
	 */
	public MessageResource $data;



	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(MessageResource $msgResource)
	{
		$this->data = $msgResource;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return PrivateChannel
	 */
	public function broadcastOn(): PrivateChannel
	{
		return new PrivateChannel("chats.{$this->data['chat_id']}");
	}

	/**
	 * The event's broadcast name.
	 *
	 * @return string
	 */
	public function broadcastAs(): string
	{
		return 'NewMessageSent';
	}
}
