<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * List all skills or search by name (public).
     * Example:
     *   GET /api/skills          -> returns all skills
     *   GET /api/skills?name=python -> searches for "python"
     */
    public function index(Request $request)
    {
        if ($request->has('name')) {
            // Case-insensitive search for skill name
            $skills = Skill::where('s_name', 'like', '%' . $request->name . '%')->get();

            if ($skills->isEmpty()) {
                return response()->json(['message' => 'No matching skills found'], 404);
            }

            return response()->json($skills);
        }

        // Default: return all skills
        return response()->json(Skill::all());
    }


    /**
     * Show a single skill (public).
     */
    public function show($id)
    {
        $skill = Skill::find($id);

        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }

        return response()->json($skill);
    }

    /**
     * Create a new skill (admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            's_name' => 'required|string|max:150|unique:skills',
            'description' => 'nullable|string',
        ]);

        $skill = Skill::create([
            's_name' => ucfirst(strtolower($request->s_name)),
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Skill created successfully',
            'skill' => $skill,
        ], 201);
    }

    /**
     * Update an existing skill (admin only).
     */
    public function update(Request $request, $id)
    {
        $skill = Skill::find($id);

        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }

        $request->validate([
            's_name' => 'sometimes|string|max:150|unique:skills,s_name,' . $id . ',skill_id',
            'description' => 'nullable|string',
        ]);

        $skill->update([
            's_name' => $request->s_name ? ucfirst(strtolower($request->s_name)) : $skill->s_name,
            'description' => $request->description ?? $skill->description,
        ]);

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $skill,
        ]);
    }

    /**
     * Delete a skill (admin only).
     */
    public function destroy($id)
    {
        $skill = Skill::find($id);

        if (!$skill) {
            return response()->json(['message' => 'Skill not found'], 404);
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
