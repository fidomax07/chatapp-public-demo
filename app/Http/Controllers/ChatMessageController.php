<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Enums\MessageType;
use Illuminate\Http\Request;
use App\Models\MessagePicture;
use App\Events\NewMessageEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MessageResource;
use App\Http\Resources\MessageCollection;
use App\Notifications\NewMessagePushNotification;

class ChatMessageController extends ApiController
{
	public function __construct()
	{
		$this->sortConfig();
	}


	/**
	 * @param Chat $chat
	 * @return MessageCollection
	 */
	public function index(Chat $chat): MessageCollection
	{
		$this->validateUserChat($chat);

		return new MessageCollection($chat->userMessages(auth()->user())
			->with('pictures.media')
			->paginate($this->getLimitPerPage())
		);
	}

	/**
	 * @param Chat $chat
	 * @param Request $req
	 * @return MessageResource
	 */
	public function text(Chat $chat, Request $req): MessageResource
	{
		$this->validateUserChat($chat);
		[$text] = array_values($req->validate(['text' => 'required|max:5000']));

		/** @var Message $message */
		$message = $chat->messages()->save(new Message([
			'sender_id' => $req->user()->id,
			'text' => $text
		]));

		$msgResource = new MessageResource($message->load(['sender']));
		NewMessageEvent::dispatch($msgResource);
		$message->chat->interlocutor($req->user())->pushNotify(new NewMessagePushNotification($msgResource));

		return $msgResource;
	}

	/**
	 * @param Chat $chat
	 * @param Request $req
	 * @return MessageResource
	 * @throws \Throwable
	 */
	public function picture(Chat $chat, Request $req): MessageResource
	{
		$this->validateUserChat($chat);

		$req->validate(['text' => 'nullable|max:5000']);
		[$pictures] = array_values($req->validate([
			'pictures' => 'required|array',
			'pictures.*' => 'image|mimes:jpg,bmp,png|max:1024'
		]));

		/** @var Message $message */
		$message = $chat->messages()->save(new Message([
			'sender_id' => $req->user()->id,
			'type' => MessageType::PICTURE,
			'text' => $req->get('text')
		]));
		$message->addPictures($pictures);

		$msgResource = new MessageResource($message->load(['sender', 'pictures.media']));
		NewMessageEvent::dispatch($msgResource);
		$message->chat->interlocutor($req->user())->pushNotify(new NewMessagePushNotification($msgResource));

		return $msgResource;
	}

	/**
	 * @param Chat $chat
	 * @param Message $message
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function destroy(Chat $chat, Message $message, Request $req): JsonResponse
	{
		$this->validateUserChat($chat);
		if (!$message->belongsToChat($chat))
			abort(403, 'Message does not belong to this chat!');
		if (!$message->sentByUser($req->user()))
			abort(403, 'User does not own this message!');

		if ($message->isPicture()) {
			$message->pictures->each(fn(MessagePicture $mp) => $mp->delete());
		}

		return response()->json([
			'deleted' => $message->delete()
		]);
	}
}
