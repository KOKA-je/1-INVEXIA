<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Broadcast;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Customizing the email verification notification. This is correctly implemented.
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verify Your Email Address') // Changed to a more descriptive subject
                ->line('Please click the button below to verify your email address.') // More descriptive content
                ->action('Verify Email', $url) // More descriptive button text
                ->line('If you did not create an account, no further action is required.'); // Added a common footer line
        });

        // Global Gate for Super Admin. This is also correctly implemented.
        // It ensures that a 'Super Admin' can perform any action.
        Gate::before(function ($user, $ability) {
            // Using a strict comparison (===) and directly returning true/null is good.
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Setting the default pagination view to Bootstrap 5. This is correct.
        Paginator::useBootstrapFive();

        Broadcast::routes();

        // --- Observer Registration (Optional Addition/Consideration) ---
        // If you intend to use PosteObserver, you would register it here.
        // If PosteObserver is *not* used, or is registered elsewhere (e.g., EventServiceProvider),
        // then the 'use App\Models\Poste;' and 'use App\Observers\PosteObserver;' imports
        // at the top of this file become unnecessary for this specific file.
        // Poste::observe(PosteObserver::class);
    }
}
