<?php

namespace Database\Factories;

use App\Models\Message;
use App\Enums\MessageType;
use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Message::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition(): array
	{
		return [
			'chat_id' => fn() => 1 /*Chat::factory()*/,
			'sender_id' => fn() => 1 /*User::factory()*/,
			'type' => MessageType::TEXT,
			'text' => $this->faker->realText(),
			'status' => MessageStatus::DELIVERED,
			'sending_at' => now()->subMinutes(rand(1, 30)),
			'delivered_at' => now()
		];
	}
}
