<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('consecutive');
            $table->date('request_date');
            $table->string('event_name');
            $table->string('section');
            $table->string('responsible');
            $table->date('service_date');
            $table->time('event_time');
            $table->time('end_time');  // Add this line
            $table->string('location');
            $table->boolean('cafam_parking');
            
            // Metro Junior fields
            $table->boolean('metro_junior_required')->default(false);
            $table->string('route')->nullable();
            $table->integer('passengers')->nullable();
            $table->time('departure_time')->nullable();
            $table->time('return_time')->nullable();
            $table->text('metro_junior_observations')->nullable();
            $table->boolean('metro_junior_confirmed')->default(false);
            
            // Aldimark fields
            $table->boolean('aldimark_required')->default(false);
            $table->text('aldimark_requirement')->nullable();
            $table->time('aldimark_time')->nullable();
            $table->text('aldimark_details')->nullable();
            $table->boolean('aldimark_confirmed')->default(false);
            
            // Maintenance fields
            $table->boolean('maintenance_required')->default(false);
            $table->text('maintenance_requirement')->nullable();
            $table->date('maintenance_setup_date')->nullable();
            $table->time('maintenance_setup_time')->nullable();
            $table->boolean('maintenance_confirmed')->default(false);
            
            // General Services fields
            $table->boolean('general_services_required')->default(false);
            $table->text('general_services_requirement')->nullable();
            $table->date('general_services_setup_date')->nullable();
            $table->time('general_services_setup_time')->nullable();
            $table->boolean('general_services_confirmed')->default(false);
            
            // Systems fields
            $table->boolean('systems_required')->default(false);
            $table->text('systems_requirement')->nullable();
            $table->date('systems_setup_date')->nullable();
            $table->time('systems_setup_time')->nullable();
            $table->text('systems_observations')->nullable();
            $table->boolean('systems_confirmed')->default(false);
            
            // Purchases fields
            $table->boolean('purchases_required')->default(false);
            $table->text('purchases_requirement')->nullable();
            $table->text('purchases_observations')->nullable();
            $table->boolean('purchases_confirmed')->default(false);
            
            // Communications fields
            $table->boolean('communications_required')->default(false);
            $table->text('communications_coverage')->nullable();
            $table->text('communications_observations')->nullable();
            $table->boolean('communications_confirmed')->default(false);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
