    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up()
        {
            Schema::create('admin_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('admins');
                $table->string('action_type');
                $table->foreignId('target_id')->nullable();
                $table->text('description');
                $table->timestamps();
            });
        }
        

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('admin_logs');
        }
    };
