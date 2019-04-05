<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs_history', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->unsignedInteger('ref_id');
            $table->string('job', 40);
            $table->integer('status')->default(0);
            $table->timestamps();

            if (Schema::hasColumn('shopify_excel_upload', '_id'))
            {
                $table->foreign('ref_id')->references('_id')->on('shopify_excel_upload');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return voids
     */
    public function down()
    {
        Schema::dropIfExists('jobs_history');
    }
}
