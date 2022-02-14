<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SmsVcNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSmsVC implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var string
	 */
	private string $userPhone;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($userPhone)
	{
		$this->userPhone = $userPhone;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$user = User::where('phone', $this->userPhone)->first();

		$user->notify(new SmsVcNotification());

		$user->recordSmsVcSent();
	}
}
