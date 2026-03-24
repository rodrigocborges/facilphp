<?php

namespace Facil\Http;

/**
 * Base interface for all middlewares in the Facil framework.
 * Any custom middleware must implement this interface.
 */
interface Middleware {
    /**
     * Handle the incoming request before it reaches the route handler.
     * If the validation fails, this method should return a Response (which halts execution).
     *
     * @return void
     */
    public function handle(): void;
}