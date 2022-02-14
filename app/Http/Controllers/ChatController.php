<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use App\Jobs\PruneChatMessages;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ChatResource;
use App\Http\Resources\ChatCollection;

class ChatController extends ApiController
{
	/**
	 * ChatController constructor.
	 */
	public function __construct()
	{
		$this->sortConfig();
	}

	/**
	 * @return ChatCollection
	 */
	public function index(): ChatCollection
	{
		return new ChatCollection(
			Chat::userIndex(auth()->user())->paginate($this->getLimitPerPage())
		);
	}

	/**
	 * @param Request $req
	 * @return ChatResource
	 */
	public function findOrCreate(Request $req): ChatResource
	{
		[$recipientId] = array_values($req->validate([
			'recipient_id' => 'required|numeric|integer'
		]));

		$chat = Chat::betweenUsers($req->user()->id, $recipientId);
		if (!$chat) {
			($chat = Chat::create())->users()->attach([$req->user()->id, $recipientId]);
		}

		return new ChatResource($chat
			->loadDependents()
			->loadUserMessagesCount($req->user())
		);
	}

	/**
	 * @param Chat $chat
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function destroy(Chat $chat, Request $req): JsonResponse
	{
		$this->validateUserChat($chat);

		PruneChatMessages::dispatch($chat, $req->user());

		return response()->json(['deleted' => true]);
	}
}
