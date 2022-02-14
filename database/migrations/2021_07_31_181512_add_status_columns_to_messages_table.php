<?php

use App\Enums\MessageStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnsToMessagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('messages', function (Blueprint $table) {
			$table->after('text', function (Blueprint $table) {
				$table->enum('status', MessageStatus::values())->default(MessageStatus::SENDING);
				$table->timestamp('sending_at')->nullable();
				$table->timestamp('delivered_at')->nullable();
				$table->timestamp('seen_at')->nullable();
			});
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('messages', function (Blueprint $table) {
			$table->dropColumn('status');
		});
	}
}
