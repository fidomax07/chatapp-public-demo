<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends ApiController
{
	/**
	 * ContactController constructor.
	 */
	public function __construct()
	{
		$this->sortConfig();
	}


	/**
	 * @param Request $req
	 * @return NotificationCollection
	 */
	public function notifications(Request $req): NotificationCollection
	{
		return new NotificationCollection(
			$req->user()->notifications()->paginate($this->getLimitPerPage())
		);
	}

	/**
	 * @param Request $req
	 * @return NotificationCollection
	 */
	public function notificationsUnread(Request $req): NotificationCollection
	{
		return new NotificationCollection(
			$req->user()->unreadNotifications()->paginate($this->getLimitPerPage())
		);
	}

	/**
	 * @param DatabaseNotification $notification
	 * @return NotificationResource
	 */
	public function notificationsRead(DatabaseNotification $notification): NotificationResource
	{
		$notification->markAsRead();

		return new NotificationResource($notification);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function notificationsReadAll(Request $req): JsonResponse
	{
		$read = $req->user()->unreadNotifications()->update(['read_at' => now()]);

		return response()->json([
			'read_count' => $read
		]);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function notificationsDestroyRead(Request $req): JsonResponse
	{
		$deleted = $req->user()->readNotifications()->delete();

		return response()->json([
			'deleted_count' => $deleted
		]);
	}

	/**
	 * @param DatabaseNotification $notification
	 * @return NotificationResource
	 */
	public function notificationsDestroy(DatabaseNotification $notification): NotificationResource
	{
		$notification->delete();

		return new NotificationResource($notification);
	}

	/**
	 * @param Request $req
	 * @return JsonResponse
	 */
	public function notificationsDestroyAll(Request $req): JsonResponse
	{
		$deleted = $req->user()->notifications()->delete();

		return response()->json([
			'deleted_count' => $deleted
		]);
	}
}
