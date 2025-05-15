<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Display a listing of the agents.
     */
    public function index()
    {
        $agents = Auth::user()->agents()->latest()->get();

        return view('agents.index', compact('agents'));
    }

    /**
     * Show the form for creating a new agent.
     */
    public function create()
    {
        return view('agents.create');
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'model_provider' => 'required|string',
            'model_name' => 'required|string',
            'platform' => 'required|string|in:windows,linux,darwin',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Encrypt API key if provided
        if (!empty($request->api_key)) {
            $data['api_key_encrypted'] = Crypt::encryptString($request->api_key);
        }

        // Create agent
        $agent = Agent::create($data);

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent created successfully.');
    }

    /**
     * Display the specified agent.
     */
    public function show(Agent $agent)
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }

        $recentTasks = $agent->tasks()->latest()->take(5)->get();

        return view('agents.show', compact('agent', 'recentTasks'));
    }

    /**
     * Show the form for editing the specified agent.
     */
    public function edit(Agent $agent)
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }

        return view('agents.edit', compact('agent'));
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'model_provider' => 'required|string',
            'model_name' => 'required|string',
            'platform' => 'required|string|in:windows,linux,darwin',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Encrypt API key if provided
        if (!empty($request->api_key)) {
            $data['api_key_encrypted'] = Crypt::encryptString($request->api_key);
        }

        $agent->update($data);

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent updated successfully.');
    }

    /**
     * Remove the specified agent from storage.
     */
    public function destroy(Agent $agent)
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }

        $agent->delete();

        return redirect()->route('agents.index')
            ->with('success', 'Agent deleted successfully.');
    }

    /**
     * Toggle the active status of the agent.
     */
    public function toggleActive(Agent $agent)
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }

        $agent->update([
            'is_active' => !$agent->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Agent status updated successfully.');
    }
}
