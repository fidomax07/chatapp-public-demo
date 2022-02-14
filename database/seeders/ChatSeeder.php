<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		$chats = Chat::factory()->count(10)->create();
		//$otherUsers = User::where('phone', '!=', '+38349549692')->pluck('id');

		$chats->take(5)->each(function(Chat $chat, $index) {
			$chat->users()->attach([1, ($index+2)]);
		});
		$chats->skip(5)->values()->each(function(Chat $chat, $index) {
			$chat->users()->attach([($index+2), ($index+3)]);
		});

		/*$firstUserChats = $chats->take(35);
		$firstUserCombos = $otherUsers->random(35);
		$firstUserChats->each(fn(Chat $chat, $i) => $chat->users()->attach([1, $firstUserCombos[$i]]));

		$firstUserChats = $firstUserChats->map(fn(Chat $chat) => $chat->id)->toArray();
		$chats = $chats->filter(fn($chat) => !in_array($chat->id, $firstUserChats))->values();
		$firstUserCombos = $firstUserCombos->toArray();
		$otherUsers = $otherUsers->filter(fn($userId) => !in_array($userId, $firstUserCombos));

		$chats->each(fn(Chat $chat, $i) => $chat->users()->attach([$firstUserCombos[$i], $otherUsers->random()]));*/
	}
}
