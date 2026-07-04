<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BRICKSPOINT — Modern Hospitality ERP')</title>
    <meta name="description" content="@yield('meta_description', 'BRICKSPOINT is a premium hospitality management ERP for boutique hotels, apart-hotels, and resorts. Streamline front desk, bookings, inventory, and staff management.')">
    <meta name="author" content="Brickspoint">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/sass/app.scss'])
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            /* Light Mode - Elegant Neutrals */
            --bg-color: #d8d6d6;
            /* Soft warm gray background */
            --text-color: #2b2225;
            /* Deep espresso for text */
            --card-bg: #ffffff;
            /* Crisp white for cards */
            --primary-color: #4a4144;
            /* Rich charcoal for accents */
            --text-primary: #6b6466;
            /* Gentle slate for body text */
            --accent-color: #8e888a;
            /* Sophisticated muted mauve */
        }

        .dark {
            /* Dark Mode - Refined Contrast */
            --bg-color: #2b2225;
            /* Deep espresso background */
            --text-color: #d8d6d6;
            /* Soft gray for text */
            --card-bg: #4a4144;
            /* Charcoal card background */
            --primary-color: #b2aeaf;
            /* Elegant silver accent */
            --text-primary: #ffffff;
            /* Crisp white for headlines */
            --accent-color: #8e888a;
            /* Muted mauve for highlights */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
            font-size: 1.05rem;
            /* Slightly upscale for readability */
            line-height: 1.6;
            /* Elegant spacing */
            letter-spacing: 0.02em;
            /* Subtle refinement */
            font-weight: 400;
            /* Balanced weight for luxury feel */
            margin: 0;
            padding: 0;
        }

        h1,
        h2,
        h3 {
            font-family: 'Poppins', sans-serif;
        }

        .text-primary {
            color: var(--primary-color);
        }

        .bg-primary {
            background-color: var(--primary-color);
        }

        .bg-card {
            background-color: var(--card-bg);
        }

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

        .preloader.hidden {
            opacity: 0;
            pointer-events: none;
        }

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

        #hero {
            background-color: var(--bg-color);
            color: var(--text-color);
            padding-top: 6rem;
            padding-bottom: 6rem;
            text-align: center;
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
        }

        #hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--text-primary);
        }

        #hero a {
            margin-top: 2rem;
            display: inline-block;
            padding: 1rem 2.5rem;
            background-color: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #hero a:hover {
            transform: scale(1.05);
            background-color: var(--accent-color);
        }

        @media (min-width: 768px) {
            #hero h1 {
                font-size: 3.75rem;
            }
        }

        #hero h1 .text-primary {
            color: var(--primary-color);
        }

        #hero p {
            margin-top: 1rem;
            font-size: 1.125rem;
            color: var(--accent-color);
        }

        @media (min-width: 768px) {
            #hero p {
                font-size: 1.25rem;
            }
        }

        #overview {
            background-color: var(--bg-color);
            color: var(--text-color);
            padding-top: 4rem;
            padding-bottom: 4rem;
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
        }

        #overview h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-primary);
        }

        @media (min-width: 768px) {
            #overview h2 {
                font-size: 2.5rem;
            }
        }

        #overview h2 .text-primary {
            color: var(--primary-color);
        }

        #overview .bg-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        #overview .bg-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-5px);
        }

        #overview i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        #overview h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        #overview p {
            font-size: 0.95rem;
            color: var(--accent-color);
        }

        #modules {
            background-color: var(--bg-color);
            color: var(--text-color);
            padding-top: 4rem;
            padding-bottom: 4rem;
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
        }

        #modules h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-primary);
        }

        @media (min-width: 768px) {
            #modules h2 {
                font-size: 2.5rem;
            }
        }

        #modules h2 .text-primary {
            color: var(--primary-color);
        }

        #modules .bg-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        #modules .bg-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-5px);
        }

        #modules i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        #modules h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        #modules h3 a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease-in-out;
        }

        #modules h3 a:hover {
            color: var(--accent-color);
        }

        #modules p {
            font-size: 0.95rem;
            color: var(--accent-color);
        }

        #contact p {
            margin-top: 1rem;
            font-size: 1.125rem;
            color: var(--accent-color);
        }

        #contact a {
            margin-top: 2rem;
            display: inline-block;
            padding: 1rem 2.5rem;
            background-color: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        #contact a:hover {
            transform: scale(1.05);
            background-color: var(--accent-color);
        }

        .theme-toggle {
            background: transparent;
            cursor: pointer;
            padding: 4px 10px 4px 4px;
            border-radius: 50px;
            transition: background 0.3s ease;
        }
        .theme-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        .dark .theme-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        .theme-toggle-track {
            width: 40px;
            height: 22px;
            background: #e9ecef;
            border-radius: 50px;
            position: relative;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }
        .dark .theme-toggle-track {
            background: #495057;
        }
        .theme-toggle-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            color: #f39c12;
        }
        .dark .theme-toggle-thumb {
            transform: translateX(18px);
            color: #6c757d;
        }
        .theme-toggle-thumb i {
            transition: transform 0.4s ease;
            font-size: 10px;
        }
        .dark .theme-toggle-thumb i {
            transform: rotate(360deg);
        }
        .theme-toggle-label {
            color: #6c757d;
            transition: color 0.3s ease;
        }
        .dark .theme-toggle-label {
            color: #adb5bd;
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
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        (() => {
            const themeToggle = document.getElementById('theme-toggle');
            if (!themeToggle) return;
            const html = document.documentElement;
            const sunIcon = document.getElementById('theme-icon-sun');
            const moonIcon = document.getElementById('theme-icon-moon');
            const themeLabel = document.getElementById('theme-label');

            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const currentTheme = storedTheme || (prefersDark ? 'dark' : 'light');

            const applyTheme = (theme) => {
                if (theme === 'dark') {
                    html.classList.add('dark');
                    html.setAttribute('data-bs-theme', 'dark');
                } else {
                    html.classList.remove('dark');
                    html.setAttribute('data-bs-theme', 'light');
                }
                if (sunIcon && moonIcon) {
                    if (theme === 'dark') {
                        sunIcon.classList.add('d-none');
                        moonIcon.classList.remove('d-none');
                    } else {
                        sunIcon.classList.remove('d-none');
                        moonIcon.classList.add('d-none');
                    }
                }
                if (themeLabel) {
                    themeLabel.textContent = theme === 'dark' ? 'Dark' : 'Light';
                }
            };

            applyTheme(currentTheme);

            themeToggle.addEventListener('click', () => {
                const next = html.classList.contains('dark') ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                applyTheme(next);
            });
        })();

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.fade-in-up').forEach(element => {
            observer.observe(element);
        });
    </script>

</body>

</html>
