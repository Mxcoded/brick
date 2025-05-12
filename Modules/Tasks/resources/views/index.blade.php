{{-- @extends('tasks::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('tasks.name') !!}</p>
@endsection --}}
<!DOCTYPE html>
<html>
<head>
    <title>Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create Task</a>
        <table class="min-w-full bg-white mt-4">
            <thead>
                <tr>
                    <th class="py-2">Task Number</th>
                    <th class="py-2">Date</th>
                    <th class="py-2">Priority</th>
                    <th class="py-2">Deadline</th>
                    <th class="py-2">Assignees</th>
                    <th class="py-2">Completed</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td class="border px-4 py-2">{{ $task->task_number }}</td>
                        <td class="border px-4 py-2">{{ $task->date->format('Y-m-d') }}</td>
                        <td class="border px-4 py-2">{{ ucfirst($task->priority) }}</td>
                        <td class="border px-4 py-2">{{ $task->deadline->format('Y-m-d') }}</td>
                        <td class="border px-4 py-2">{{ $task->employees->pluck('full_name')->implode(', ') }}</td>
                        <td class="border px-4 py-2">{{ $task->is_completed ? 'Yes' : 'No' }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('tasks.edit', $task->id) }}" class="text-blue-500">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>