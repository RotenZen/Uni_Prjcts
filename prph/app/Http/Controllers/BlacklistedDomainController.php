<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlacklistedDomain;

class BlacklistedDomainController extends Controller
{
    /**
     * List all blacklisted domains (admin only)
     */
    public function index()
    {
        return response()->json(BlacklistedDomain::all());
    }

    /**
     * Add a new blacklisted domain (admin only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255|unique:blacklisted_domains,domain',
            'reason' => 'nullable|string|max:255',
        ]);

        $domain = BlacklistedDomain::create([
            'domain' => strtolower(trim($request->domain)),
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Domain added to blacklist successfully.',
            'data' => $domain,
        ], 201);
    }

    /**
     * Delete a domain from blacklist (admin only)
     */
    public function destroy($key)
    {
        $key = strtolower(trim($key));

        $entry = BlacklistedDomain::where('domain', $key)
            ->orWhere('id', $key)
            ->first();

        if (!$entry) {
            return response()->json(['message' => 'Domain not found'], 404);
        }

        $entry->delete();

        return response()->json(['message' => 'Domain removed from blacklist.']);
    }

}
