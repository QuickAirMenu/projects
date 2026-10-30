<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectAttachmentController extends Controller
{
    /**
     * Upload and store a new project attachment.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $path = $file->store('projects/'.$project->id, 'private');

        $project->attachments()->create([
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', __('project.attachment_uploaded'));
    }

    /**
     * Delete a project attachment.
     */
    public function destroy(ProjectAttachment $attachment)
    {
        $this->authorize('update', $attachment->project);

        Storage::disk('private')->delete($attachment->file_path);
        $projectId = $attachment->project_id;
        $attachment->delete();

        return redirect()->route('projects.show', $projectId)
            ->with('success', __('project.attachment_deleted'));
    }

    /**
     * Download a project attachment (requires view permission on the project).
     */
    public function download(ProjectAttachment $attachment)
    {
        $this->authorize('view', $attachment->project);

        if (! $attachment->file_path) {
            abort(404);
        }

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->file_name ?? basename($attachment->file_path)
        );
    }
}
