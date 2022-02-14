<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Enums\MessageType;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws \Throwable
	 */
	public function run()
	{
		Chat::withDependents()->limit(1)->get()->each(function (Chat $chat) {
			$userA = $chat->users->first()->id;
			$userB = $chat->users->last()->id;

			Message::factory()
				->count(10)
				->make(['chat_id' => $chat->id])
				->each(function (Message $m, $i) use ($userA, $userB) {
					//$type = ($i == 0 || $i % 3 != 0) ? MessageType::TEXT : MessageType::PICTURE;
					$type = MessageType::TEXT;

					sleep(1);
					$m->fill([
						'sender_id' => $i % 2 == 0 ? $userA : $userB,
						'type' => $type,
					])->save();

					if ($type == MessageType::PICTURE) {
						collect(range(1, rand(1, 3)))->each(
							fn($n) => $m->addPicture($this->getPictureFile($n), true)
						);
					}
				});
		});
	}

	/**
	 * @param int $picNumber
	 * @return string
	 */
	private function getPictureFile(int $picNumber): string
	{
		return storage_path("app/public/message/picture/sample_msg_pic$picNumber.jpg");
	}
}
