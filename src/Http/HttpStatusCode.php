<?php

namespace Facil\Http;

/**
 * Enum representing common HTTP status codes.
 * Prevents hardcoding magic numbers across the application.
 */
enum HttpStatusCode: int {
    // 2xx Success
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;

    // 3xx Redirection
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case NOT_MODIFIED = 304;

    // 4xx Client Errors
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422; // Great for validation errors
    case TOO_MANY_REQUESTS = 429;

    // 5xx Server Errors
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
}