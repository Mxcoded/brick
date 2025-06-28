<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Gym Module - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <title>Gym Membership Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">


    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap5.min.css">
    <!-- Before </body> -->
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin: 2rem auto;
            max-width: 900px;
        }

        .form-header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.2rem;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-body {
            padding: 2.5rem;
        }

        .section-title {
            color: var(--secondary);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary);
        }

        .form-card {
            background: #f8fbff;
            border-radius: 12px;
            border: 1px solid #e1e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .form-card:hover {
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
            border-color: #c2d5ff;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .required:after {
            content: " *";
            color: var(--danger);
        }

        .btn-primary {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .hidden {
            display: none;
        }

        .toggle-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .toggle-option {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-option.active {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .toggle-option i {
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            color: var(--primary);
        }

        .member-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e1e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
        }

        .member-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .member-icon {
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 1.25rem;
        }

        .terms-container {
            background: #f8fbff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .form-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            margin-top: 1rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .form-body {
                padding: 1.5rem;
            }

            .toggle-container {
                flex-direction: column;
            }
        }
    </style>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b35;
            --secondary: #2a9d8f;
            --dark: #1a1a2e;
            --darker: #0d0d1a;
            --light: #f8f9fa;
            --card-bg: #16213e;
            --accent: #4cc9f0;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker) 0%, var(--light) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--light);
            padding: 20px;
        }
        
        .form-container {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            margin: 2rem auto;
            max-width: 1000px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-header {
            background: linear-gradient(120deg, var(--primary), #e64a19);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .form-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .form-header h1 {
            font-weight: 800;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .form-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            font-weight: 500;
        }
        
        .logo-badge {
            background: rgba(0,0,0,0.2);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255,255,255,0.3);
        }
        
        .form-body {
            padding: 2.5rem;
        }
        
        .section-title {
            color: var(--accent);
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            background: rgba(42, 157, 143, 0.2);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-card {
            background: rgba(26, 26, 46, 0.7);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .form-card:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }
        
        .required:after {
            content: " *";
            color: var(--primary);
        }
        
        .btn-primary {
            background: linear-gradient(120deg, var(--primary), #e64a19);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(255, 107, 53, 0.4);
        }
        
        .btn-outline-light {
            border: 2px solid var(--light);
            color: var(--light);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background: var(--light);
            color: var(--dark);
            transform: translateY(-2px);
        }
        
        .form-control, .form-select {
            background: rgba(10, 10, 20, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light);
            padding: 0.75rem;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(10, 10, 20, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
            color: var(--light);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .hidden {
            display: none;
        }
        
        .toggle-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .toggle-option {
            flex: 1;
            text-align: center;
            padding: 1.5rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(10, 10, 20, 0.3);
        }
        
        .toggle-option:hover {
            background: rgba(42, 157, 143, 0.1);
        }
        
        .toggle-option.active {
            border-color: var(--primary);
            background: rgba(255, 107, 53, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
            transform: translateY(-5px);
        }
        
        .toggle-option i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--accent);
        }
        
        .toggle-option h5 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .member-card {
            background: rgba(22, 33, 62, 0.7);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .member-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }
        
        .member-icon {
            width: 50px;
            height: 50px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        
        .terms-container {
            background: rgba(10, 10, 20, 0.5);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .form-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-group-text {
            background: rgba(10, 10, 20, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--accent);
        }
        
        .form-check-input {
            background-color: rgba(10, 10, 20, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .alert {
            border-radius: 12px;
        }
        
        .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .radio-group .form-check {
            background: rgba(10, 10, 20, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .radio-group .form-check:hover {
            border-color: var(--secondary);
        }
        
        .radio-group .form-check-input {
            margin-top: 0.35rem;
        }
        
        @media (max-width: 768px) {
            .form-body {
                padding: 1.5rem;
            }
            
            .toggle-container {
                flex-direction: column;
            }
            
            .form-header {
                padding: 1.5rem 1rem;
            }
            
            .form-header h1 {
                font-size: 2rem;
            }
        }
        
        .progress-container {
            margin-bottom: 2rem;
        }
        
        .progress {
            height: 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            overflow: visible;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
    </style> --}}

</head>

<body>
    @include('admin::layouts.navbar')
    @yield('content')

    {{-- Vite JS --}}
    {{-- {{ module_vite('build-gym', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}


    <!-- Before </body> -->
    <!-- In <head> -->

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</body>
