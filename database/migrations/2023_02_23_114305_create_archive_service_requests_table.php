<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchiveServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_service_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cvm_id')->nullable();
            $table->string('import_id')->nullable();
            $table->string('request_type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('hospital_id')->nullable();
            $table->string('dept_id')->nullable();
            $table->text('remarks')->nullable();
            $table->text('closure_remarks')->nullable(); 
            $table->string('sap_id')->nullable();
            $table->string('sfdc_id')->nullable();
            $table->string('sfdc_customer_id')->nullable();
            $table->string('product_category')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('last_updated_by')->nullable();
            $table->string('status')->nullable();
            $table->tinyInteger('is_escalated')->default(0);
            $table->integer('escalation_count')->nullable();  
            $table->string('escalation_assign1')->nullable();
            $table->string('escalation_assign2')->nullable();
            $table->string('escalation_assign3')->nullable();
            $table->string('escalation_assign4')->nullable();
            $table->text('escalation_reasons')->nullable();
            $table->text('escalation_remarks')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->string('feedback_id')->nullable(); 
            $table->integer('feedback_requested')->default(0);
            $table->tinyInteger('is_practice')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_service_requests');
    }
}
