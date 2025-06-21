<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Password breach checks table
        Schema::create('password_breach_checks', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('password_hash')->nullable();
            $table->json('breach_results')->nullable();
            $table->integer('breach_count')->default(0);
            $table->string('risk_level')->default('low'); // low, medium, high
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['email', 'created_at']);
        });

        // IP reputation checks table
        Schema::create('ip_reputation_checks', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('isp')->nullable();
            $table->integer('threat_score')->nullable();
            $table->json('flags')->nullable(); // proxy, vpn, tor, etc.
            $table->string('recommendation')->default('allow'); // allow, monitor, block
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['ip_address', 'created_at']);
        });

        // Malware scan results table
        Schema::create('malware_scan_results', function (Blueprint $table) {
            $table->id();
            $table->string('scan_type'); // file, url
            $table->string('target'); // filename or URL
            $table->string('file_hash')->nullable();
            $table->integer('detection_ratio')->nullable();
            $table->integer('total_engines')->nullable();
            $table->string('threat_level')->default('low'); // low, medium, high
            $table->json('detections')->nullable();
            $table->string('recommendation')->default('allow'); // allow, quarantine, block
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['scan_type', 'target', 'created_at']);
        });

        // Dark web monitoring table
        Schema::create('dark_web_monitoring', function (Blueprint $table) {
            $table->id();
            $table->string('search_type'); // email, domain
            $table->string('target'); // email or domain
            $table->integer('breach_count')->default(0);
            $table->date('last_breach_date')->nullable();
            $table->json('breaches')->nullable();
            $table->string('recommendation')->default('safe'); // safe, monitor, immediate_action
            $table->json('action_items')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['search_type', 'target', 'created_at']);
        });

        // Cursor analytics table
        Schema::create('cursor_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('session_key')->index();
            $table->string('user_id')->nullable()->index();
            $table->string('page_url');
            $table->integer('x_position');
            $table->integer('y_position');
            $table->string('event_type'); // click, hover, move, scroll
            $table->string('element_id')->nullable();
            $table->string('element_class')->nullable();
            $table->integer('velocity')->nullable(); // pixels per second
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['session_key', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Session replay table
        Schema::create('session_replays', function (Blueprint $table) {
            $table->id();
            $table->string('session_key')->unique();
            $table->string('user_id')->nullable()->index();
            $table->string('page_url');
            $table->json('events')->nullable();
            $table->json('screenshots')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('status')->default('recording'); // recording, completed, archived
            $table->json('analytics')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // Security alerts table
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type'); // password_breach, suspicious_ip, malware_detected, dark_web_breach
            $table->string('severity'); // low, medium, high, critical
            $table->string('title');
            $table->text('description');
            $table->json('data')->nullable();
            $table->string('status')->default('new'); // new, acknowledged, resolved
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('acknowledged_by')->nullable();
            $table->string('resolved_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['alert_type', 'severity', 'status']);
            $table->index(['status', 'created_at']);
        });

        // API rate limiting table
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('endpoint');
            $table->integer('hits')->default(1);
            $table->timestamp('reset_at');
            $table->timestamps();
            
            $table->unique(['key', 'endpoint']);
            $table->index(['reset_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('security_alerts');
        Schema::dropIfExists('session_replays');
        Schema::dropIfExists('cursor_analytics');
        Schema::dropIfExists('dark_web_monitoring');
        Schema::dropIfExists('malware_scan_results');
        Schema::dropIfExists('ip_reputation_checks');
        Schema::dropIfExists('password_breach_checks');
    }
}; 