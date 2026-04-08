<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-User-Id');

        if (! $userId) {
            return response()->json([
                'message' => 'Unauthenticated. Provide X-User-Id header.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()->find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated. User not found.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }
}
