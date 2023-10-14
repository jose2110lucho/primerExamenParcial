<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        User::create([
            'name'=>'Pedro Ch',
            'email'=>'pedro22enriqu@gmail.com',
            'password'=>Hash::make('123123123'),
        ]);
        User::create([
            'name'=>'Enrique Ch',
            'email'=>'pedrito2enrique@gmail.com',
            'password'=>Hash::make('123123123'),
        ]);
        User::create([
            'name'=>'jose',
            'email'=>'jpadillayapura@gmail.com',
            'password'=>Hash::make('211047864'),
        ]);
        User::create([
            'name'=>'zuleny',
            'email'=>'leny@gmail.com',
            'password'=>Hash::make('111'),
        ]);
        User::create([
            'name'=>'stephany',
            'email'=>'teffy@gmail.com',
            'password'=>Hash::make('222'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
