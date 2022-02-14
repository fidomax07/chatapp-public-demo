<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageStatusController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/admin/pin/reset', [AuthController::class, 'pinReset']);


Route::post('register', [AuthController::class, 'register']);
Route::post('register/verify', [AuthController::class, 'registerVerify']);
Route::post('smsvc', [AuthController::class, 'smsVc']);
Route::post('smsvc/reset', [AuthController::class, 'smsVcReset']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	Route::get('me', [AuthController::class, 'me']);
	Route::post('logout', [AuthController::class, 'logout']);

	Route::post('broadcasting/auth', [AuthController::class, 'broadcastingAuth']);
	Route::post('fcm-token', [AuthController::class, 'fcmToken']);
	Route::delete('fcm-token/{id}', [AuthController::class, 'fcmTokenDestroy'])->whereNumber('id');

	/*Route::post('/dev/db/mfs', function () {
		if (!App::environment('local')) {
			abort(406, 'Not Allowed!');
		}
		\App\Models\MessagePicture::all()->each(fn($mp) => $mp->delete());
		$exitCode = Artisan::call('migrate:fresh --seed');
		return response()->json(['result' => $exitCode]);
	});*/

	Route::prefix('settings/user')->group(function () {
		Route::post('avatar', [SettingsController::class, 'avatar']);
		Route::put('username', [SettingsController::class, 'username']);
		Route::put('name', [SettingsController::class, 'name']);
		Route::put('pin', [SettingsController::class, 'pin']);
		Route::put('notifications', [SettingsController::class, 'notifications']);
		Route::delete('destroy', [SettingsController::class, 'destroy']);
	});

	Route::get('contacts', [ContactController::class, 'index']);
	Route::post('contacts', [ContactController::class, 'store']);
	Route::get('contacts/{contact}', [ContactController::class, 'show']);
	Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);

	Route::get('chats', [ChatController::class, 'index']);
	Route::post('chats/find-or-create', [ChatController::class, 'findOrCreate']);
	Route::delete('chats/{chat}', [ChatController::class, 'destroy']);
	Route::get('chats/{chat}/messages', [ChatMessageController::class, 'index']);
	Route::post('chats/{chat}/messages/text', [ChatMessageController::class, 'text']);
	Route::post('chats/{chat}/messages/picture', [ChatMessageController::class, 'picture']);
	Route::delete('chats/{chat}/messages/{message}', [ChatMessageController::class, 'destroy']);

	Route::post('messages/statuses/delivered', [MessageStatusController::class, 'delivered']);
	Route::post('messages/statuses/seen', [MessageStatusController::class, 'seen']);

	Route::get('notifications', [NotificationController::class, 'notifications']);
	Route::get('notifications/unread', [NotificationController::class, 'notificationsUnread']);
	Route::put('notifications/{notification}/read', [NotificationController::class, 'notificationsRead']);
	Route::put('notifications/read', [NotificationController::class, 'notificationsReadAll']);
	Route::delete('notifications/read', [NotificationController::class, 'notificationsDestroyRead']);
	Route::delete('notifications/{notification}', [NotificationController::class, 'notificationsDestroy']);
	Route::delete('notifications', [NotificationController::class, 'notificationsDestroyAll']);
});

