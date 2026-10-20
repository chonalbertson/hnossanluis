<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('quotes.view') || $user->hasPermission('quotes.manage');
    }

    public function view(User $user, Quote $quote): bool
    {
        if ($user->hasPermission('quotes.view') || $user->hasPermission('quotes.manage')) {
            return true;
        }

        return $quote->user_id !== null && (int) $quote->user_id === (int) $user->id;
    }

    public function create(?User $user = null): bool
    {
        return true;
    }

    public function update(User $user, Quote $quote): bool
    {
        return $user->hasPermission('quotes.manage');
    }

    public function respond(User $user, Quote $quote): bool
    {
        return $user->hasPermission('quotes.respond') || $user->hasPermission('quotes.manage');
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->isAdmin();
    }
}
