<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentTask;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AgentTaskController extends Controller
{
    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * Display a listing of the agent tasks.
     */
    public function index()
    {
        $tasks = AgentTask::where('user_id', Auth::id())->with('agent')->latest()->paginate(15);

        return view('agent-tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new agent task.
     */
    public function create(Request $request)
    {
        $agentId = $request->input('agent_id');
        $agent = null;

        if ($agentId) {
            $agent = Agent::where('user_id', Auth::id())->findOrFail($agentId);
        }

        $agents = Agent::where('user_id', Auth::id())->where('is_active', true)->get();

        return view('agent-tasks.create', compact('agents', 'agent'));
    }

    /**
     * Store a newly created agent task in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'instruction' => 'required|string',
        ]);

        $agent = Agent::where('user_id', Auth::id())->findOrFail($request->agent_id);

        $task = new AgentTask();
        $task->agent_id = $agent->id;
        $task->user_id = Auth::id();
        $task->instruction = $request->instruction;
        $task->status = 'pending';
        $task->save();

        // Process the task using the agent service
        try {
            $result = $this->agentService->processTask($task);

            if (!$result['success']) {
                return redirect()->route('agent-tasks.show', $task)
                    ->with('error', $result['message']);
            }

            return redirect()->route('agent-tasks.show', $task)
                ->with('success', 'Tác vụ đã được tạo và xử lý thành công.');
        } catch (\Exception $e) {
            Log::error('Error processing task: ' . $e->getMessage());

            return redirect()->route('agent-tasks.show', $task)
                ->with('error', 'Đã xảy ra lỗi khi xử lý tác vụ: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified agent task.
     */
    public function show(AgentTask $agentTask)
    {
        if ($agentTask->user_id !== Auth::id()) {
            abort(403);
        }

        $agentTask->load('agent', 'actions');

        // Xử lý để hiển thị ảnh chụp màn hình nếu có
        $debugInfo = [];

        foreach ($agentTask->actions as $action) {
            $actionDebug = [
                'action_type' => $action->action_type,
                'action_data' => $action->action_data
            ];

            if ($action->action_type === 'screenshot' && isset($action->action_data['storage_path'])) {
                $path = $action->action_data['storage_path'];
                $actionDebug['storage_path'] = $path;
                $actionDebug['exists_in_storage'] = Storage::disk('public')->exists($path);

                if (Storage::disk('public')->exists($path)) {
                    $action->screenshot_url = Storage::url($path);
                    $actionDebug['screenshot_url'] = $action->screenshot_url;
                } else {
                    $action->error = "Ảnh không tồn tại trong storage";
                    $actionDebug['error'] = $action->error;
                }
            }

            $debugInfo[] = $actionDebug;
        }

        return view('agent-tasks.show', [
            'task' => $agentTask,
            'debugInfo' => $debugInfo
        ]);
    }

    /**
     * Cancel the specified agent task.
     */
    public function cancel(AgentTask $agentTask)
    {
        if ($agentTask->user_id !== Auth::id()) {
            abort(403);
        }

        if ($this->agentService->cancelTask($agentTask)) {
            return redirect()->back()->with('success', 'Tác vụ đã được hủy thành công.');
        }

        return redirect()->back()->with('error', 'Không thể hủy tác vụ.');
    }

    /**
     * Remove the specified agent task from storage.
     */
    public function destroy(AgentTask $agentTask)
    {
        if ($agentTask->user_id !== Auth::id()) {
            abort(403);
        }

        $agentTask->delete();

        return redirect()->route('agent-tasks.index')
            ->with('success', 'Tác vụ đã được xóa thành công.');
    }
}
