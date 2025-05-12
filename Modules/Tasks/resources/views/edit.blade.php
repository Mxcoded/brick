<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Edit Task</h1>
        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700">Completed</label>
                <select name="is_completed" class="w-full p-2 border rounded" required>
                    <option value="1" {{ $task->is_completed ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$task->is_completed ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Completion Date</label>
                <input type="date" name="completion_date" value="{{ $task->completion_date ? $task->completion_date->format('Y-m-d') : '' }}" class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Notes</label>
                <textarea name="notes" class="w-full p-2 border rounded">{{ $task->notes }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Reason for Non-Completion</label>
                <textarea name="non_completion_reason" class="w-full p-2 border rounded">{{ $task->non_completion_reason }}</textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Task</button>
        </form>

        @if (Auth::user()->is_general_manager) <!-- Assuming role check -->
            <h2 class="text-xl font-bold mt-8">Evaluate Task</h2>
            <form action="{{ route('tasks.evaluate', $task->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700">Successfully Completed</label>
                    <select name="is_successful" class="w-full p-2 border rounded" required>
                        <option value="1" {{ $task->is_successful ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !$task->is_successful ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Meets Expectations</label>
                    <select name="meets_expectations" class="w-full p-2 border rounded" required>
                        <option value="1" {{ $task->meets_expectations ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !$task->meets_expectations ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">General Manager Notes</label>
                    <textarea name="gm_notes" class="w-full p-2 border rounded">{{ $task->gm_notes }}</textarea>
                </div>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Evaluate Task</button>
            </form>
        @endif
    </div>
</body>
</html>