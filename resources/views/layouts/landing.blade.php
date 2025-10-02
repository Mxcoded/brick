<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRICKSPOINT // Modern ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            /* Light Mode */
            --bg-color: #f5f5f5;
            --text-color: #1d1f21;
            --card-bg: #ffffff;
            --primary-color: #f19a05;
            --text-primary: #1a1a1a;
            --accent-color: #e49504;

        }

        .dark {
            /* Dark Mode */
            --bg-color: #1d1f21;
            --text-color: #f5f5f5;
            --card-bg: #2d3034;
            --primary-color: #d87504;
            --text-primary: #f5f5f5;
            --accent-color: #e49504;

        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
        }
        h1, h2, h3 { font-family: 'Poppins', sans-serif; }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .bg-card { background-color: var(--card-bg); }

        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-color);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-in-out;
            opacity: 1;
        }
        .preloader.hidden { opacity: 0; pointer-events: none; }
        
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div id="preloader" class="preloader">
    <div class="flex items-center space-x-2 text-3xl font-bold">
        <div class="w-4 h-4 rounded-full bg-primary animate-bounce" style="animation-delay: 0s;"></div>
        <div class="w-4 h-4 rounded-full bg-primary animate-bounce" style="animation-delay: 0.2s;"></div>
        <div class="w-4 h-4 rounded-full bg-primary animate-bounce" style="animation-delay: 0.4s;"></div>
    </div>
</div>

@include('components.header')

<main class="container mx-auto px-4 py-16">
    @yield('content')
</main>

@include('components.footer')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const preloader = document.getElementById('preloader');
        preloader.classList.add('hidden');
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });

    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;

    const systemThemeIsDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme === 'dark' || (!currentTheme && systemThemeIsDark)) {
        html.classList.add('dark');
        themeToggle.querySelector('i').className = 'fas fa-sun';
    } else {
        html.classList.remove('dark');
        themeToggle.querySelector('i').className = 'fas fa-moon';
    }

    themeToggle.addEventListener('click', () => {
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            themeToggle.querySelector('i').className = 'fas fa-moon';
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            themeToggle.querySelector('i').className = 'fas fa-sun';
            localStorage.setItem('theme', 'dark');
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(element => {
        observer.observe(element);
    });
</script>

</body>
</html>